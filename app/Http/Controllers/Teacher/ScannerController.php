<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lecture;
use App\Models\Warning;
use App\Models\Student;
use App\Models\Attendance;
use Inertia\Inertia;
use App\Jobs\ProcessLectureAbsences;

class ScannerController extends Controller
{

    public function show($id)
    {
        $lecture = Lecture::with(['subject', 'group'])->findOrFail($id);
        
        // Ensure only the assigned teacher can open the scanner
        if ($lecture->teacher_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('Teacher/Scanner/Show', [
            'lecture' => $lecture
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
                'message' => 'عذراً، لا يمكن تسجيل الحضور بعد مرور 24 ساعة على المحاضرة.'
            ], 403);
        }

        try {
            $studentExternalId = \Illuminate\Support\Facades\Crypt::decryptString($request->qr_payload);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json(['success' => false, 'message' => 'رمز QR غير صالح أو مساوم عليه.'], 400);
        }

        $student = \App\Models\Student::where('student_external_id', $studentExternalId)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على الطالب في النظام.'], 404);
        }

        // Check if student belongs to this lecture's group (stage and study_type are derived from group)
        if ($student->group_id !== $lecture->group_id) {
            return response()->json(['success' => false, 'message' => 'الطالب لا ينتمي لمجموعة هذه المحاضرة.'], 400);
        }

        // Check for duplicate scan
        $existingAttendance = \App\Models\Attendance::where('lecture_id', $lecture->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttendance) {
            return response()->json(['success' => false, 'message' => 'تم تسجيل حضور هذا الطالب مسبقاً.'], 400);
        }

        // Record Attendance
        $attendance = Attendance::create([
            'lecture_id' => $lecture->id,
            'student_id' => $student->id,
            'status' => 'present',
            'check_in_method' => 'qr',
            'check_in_at' => now(),
        ]);

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
                'name' => $student->first_name . ' ' . $student->last_name,
                'external_id' => $student->student_external_id,
                'time' => now()->format('h:i:s A')
            ]
        ]);
    }
}


