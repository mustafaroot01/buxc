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

        $totalStudents = Student::where('group_id', $lecture->group_id)->count();
        
        return Inertia::render('Teacher/Scanner/Show', [
            'lecture' => $lecture,
            'initial_students' => $attendances,
            'total_students' => $totalStudents,
        ]);
    }

    public function close(Request $request, $id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== auth()->id()) {
            abort(403);
        }

        $lecture->update(['status' => 'closed']);

        // --- Fix: Dispatch to background queue to process absences without blocking the UI ---
        ProcessLectureAbsences::dispatch($lecture);

        return redirect()->route('teacher.dashboard')->with('success', 'تم إنهاء المحاضرة بنجاح ويجري معالجة غياب الطلاب في الخلفية.');
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

        // --- Fix: 24-Hour Edit Lock Check (Allow only if it has not been more than 24 hours since start time) ---
        if ($lecture->start_time->addHours(24)->isPast()) {
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

        // Unified Fix: Check for existing record (even thrashed) and handle restoration
        $existing = Attendance::withTrashed()
            ->where('lecture_id', $lecture->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            if ($existing->status === 'present' && ! $existing->trashed()) {
                return response()->json(['success' => false, 'message' => 'تم تسجيل حضور هذا الطالب مسبقاً.'], 400);
            }

            if ($existing->trashed()) {
                $existing->restore();
            }

            $existing->update([
                'status' => 'present',
                'check_in_method' => 'qr',
                'check_in_at' => now(),
            ]);
            $attendance = $existing;
        } else {
            $attendance = Attendance::create([
                'lecture_id' => $lecture->id,
                'student_id' => $student->id,
                'status' => 'present',
                'check_in_method' => 'qr',
                'check_in_at' => now(),
            ]);
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
                'name' => trim("{$student->first_name} {$student->second_name} {$student->last_name}"),
                'external_id' => $student->student_external_id,
                'time' => now()->format('h:i:s A'),
            ],
        ]);
    }
}
