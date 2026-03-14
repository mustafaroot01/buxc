<?php

namespace App\Http\Controllers\Api;

use App\Events\StudentScanned;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\Warning;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    use ApiResponse;

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_payload' => 'required|string',
            'lecture_id' => 'required|exists:lectures,id',
        ]);

        $lecture = Lecture::findOrFail($validated['lecture_id']);

        // 1. Session Lock: Check if lecture is still active
        if ($lecture->status !== 'active') {
            return $this->error('عذراً، جلسة هذه المحاضرة مغلقة حالياً.', 403);
        }

        // 2 & 3. Look up Student directly by the 24-char unique qr_payload
        $student = Student::where('qr_payload', $validated['qr_payload'])->first();

        if (! $student) {
            return $this->error('رمز QR غير صالح أو مزور! يرجى التأكد من المصدر.', 400); // Red Alert case
        }

        // 4. Duplicate Check
        $existingAttendance = Attendance::where('lecture_id', $lecture->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttendance) {
            return $this->success([
                'student' => [
                    'name' => $student->full_name,
                    'time' => $existingAttendance->check_in_at->format('H:i'),
                ],
            ], 'هذا الطالب مسجل حضوره مسبقاً في هذه المحاضرة.', 200); // Changed to success for better UX in app, or keep 409 if needed. I'll use success but with clear message.
        }

        // 5. Mark Attendance (Unified Fix: Explicitly check for thrashed and update)
        $attendance = Attendance::withTrashed()
            ->where('lecture_id', $lecture->id)
            ->where('student_id', $student->id)
            ->first();

        if ($attendance) {
            if ($attendance->trashed()) {
                $attendance->restore();
            }
            $attendance->update([
                'status' => 'present',
                'check_in_at' => Carbon::now(),
                'check_in_method' => 'qr',
            ]);
        } else {
            $attendance = Attendance::create([
                'lecture_id' => $lecture->id,
                'student_id' => $student->id,
                'status' => 'present',
                'check_in_at' => Carbon::now(),
                'check_in_method' => 'qr',
            ]);
        }

        // 6. Warning Logic: Reset streak and resolve active warnings
        if ($student->consecutive_absences > 0) {
            $student->update(['consecutive_absences' => 0]);

            Warning::where('student_id', $student->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);
        }

        $studentData = [
            'name' => $student->full_name,
            'photo_url' => $student->photo_path ? asset('storage/'.$student->photo_path) : null,
            'time' => $attendance->check_in_at->format('H:i'),
        ];

        // Broadcast the real-time event to the frontend (Web Dashboard)
        try {
            broadcast(new StudentScanned($lecture->id, $studentData));
        } catch (\Exception $e) {
            // Silently fail broadcasting errors to prevent blocking the scanner's response
            \Illuminate\Support\Facades\Log::warning("Broadcasting failed for student {$student->id} in lecture {$lecture->id}: " . $e->getMessage());
        }

        return $this->success($studentData, 'تم تسجيل حضور الطالب بنجاح.');
    }
}
