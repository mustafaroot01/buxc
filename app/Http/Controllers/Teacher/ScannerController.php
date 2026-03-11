<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLectureAbsences;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\Warning;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScannerController extends Controller
{
    public function show($id)
    {
        $lecture = Lecture::with(['subject', 'group'])->findOrFail($id);

        // Ensure only the assigned teacher can open the scanner
        if ($lecture->teacher_id !== auth()->id()) {
            abort(403);
        }

        // Fetch already scanned students to populate the live feed on initial load
        $attendances = Attendance::with('student')
            ->where('lecture_id', $lecture->id)
            ->where('status', 'present')
            ->orderBy('check_in_at', 'desc')
            ->get()
            ->map(function ($attendance) {
                return [
                    'name' => $attendance->student->full_name,
                    'time' => $attendance->check_in_at->format('h:i A'),
                    'external_id' => $attendance->student->student_external_id,
                ];
            });

        return Inertia::render('Teacher/Scanner/Show', [
            'lecture' => $lecture,
            'initial_students' => $attendances,
        ]);
    }

    public function close(Request $request, $id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== auth()->id()) {
            abort(403);
        }

        $lecture->update(['status' => 'closed']);

        // Dispatch background job to process absences and warnings
        ProcessLectureAbsences::dispatch($lecture);

        return redirect()->route('teacher.dashboard')->with('success', 'Lecture session closed successfully.');
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'qr_payload' => 'required|string',
        ]);

        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($lecture->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'هذه المحاضرة مغلقة ولا يمكن تسجيل الحضور فيها.'], 400);
        }

        // 24-Hour Edit Lock Check
        if (\Carbon\Carbon::parse($lecture->start_time)->diffInHours(now()) >= 24) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا يمكن تسجيل الحضور بعد مرور 24 ساعة على المحاضرة.',
            ], 403);
        }

        // Look up student directly by their unique qr_payload token
        $student = \App\Models\Student::where('qr_payload', $request->qr_payload)->first();

        if (! $student) {
            return response()->json(['success' => false, 'message' => 'رمز QR غير صالح أو الطالب غير موجود.'], 400);
        }

        // Check if student belongs to this lecture's group (stage and study_type are derived from group)
        if ($student->group_id !== $lecture->group_id) {
            return response()->json(['success' => false, 'message' => 'الطالب لا ينتمي لمجموعة هذه المحاضرة.'], 400);
        }

        // Sub-50ms Optimization: Rely entirely on the physical UNIQUE constraint in the DB.
        // No heavy SELECT query to check for duplicates before inserting.
        try {
            Attendance::create([
                'lecture_id' => $lecture->id,
                'student_id' => $student->id,
                'status' => 'present',
                'check_in_method' => 'qr',
                'check_in_at' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Caught by the anti-duplication shield (UNIQUE(lecture_id, student_id))
            return response()->json(['success' => false, 'message' => 'تم تسجيل حضور هذا الطالب مسبقاً.'], 400);
        } catch (\Illuminate\Database\QueryException $e) {
            // Fallback for some DB drivers that might not throw the specific UniqueConstraint exception
            if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 19)) {
                return response()->json(['success' => false, 'message' => 'تم تسجيل حضور هذا الطالب مسبقاً.'], 400);
            }
            throw $e;
        }

        // Warning Logic: Reset streak and resolve active warnings
        if ($student->consecutive_absences > 0) {
            $student->update(['consecutive_absences' => 0]);

            Warning::where('student_id', $student->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);
        }

        // (Optional) Fire event to WebSocket here

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحضور بنجاح',
            'student' => [
                'name' => $student->first_name.' '.$student->last_name,
                'external_id' => $student->student_external_id,
                'time' => now()->format('h:i:s A'),
            ],
        ]);
    }
}
