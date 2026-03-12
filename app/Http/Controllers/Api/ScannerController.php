<?php

namespace App\Http\Controllers\Api;

use App\Events\StudentScanned;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\Warning;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_payload' => 'required|string',
            'lecture_id' => 'required|exists:lectures,id',
        ]);

        $lecture = Lecture::findOrFail($validated['lecture_id']);

        // 1. Session Lock: Check if lecture is still active
        if ($lecture->status !== 'active') {
            return response()->json(['error' => 'This lecture session is closed.'], 403);
        }

        // 2 & 3. Look up Student directly by the 24-char unique qr_payload
        $student = Student::where('qr_payload', $validated['qr_payload'])->first();

        if (! $student) {
            return response()->json(['error' => 'Invalid or forged QR Code.'], 400); // Red Alert case
        }

        // 4. Duplicate Check
        $existingAttendance = Attendance::where('lecture_id', $lecture->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Student already scanned in.',
                'student' => [
                    'name' => $student->full_name,
                    'time' => $existingAttendance->check_in_at->format('H:i'),
                ],
            ], 409); // 409 Conflict for Yellow Alert case
        }

        // 5. Mark Attendance
        $attendance = Attendance::create([
            'lecture_id' => $lecture->id,
            'student_id' => $student->id,
            'status' => 'present',
            'check_in_at' => Carbon::now(),
            'check_in_method' => 'qr',
        ]);

        // 6. Warning Logic: Reset streak and resolve active warnings
        if ($student->consecutive_absences > 0) {
            $student->update(['consecutive_absences' => 0]);

            Warning::where('student_id', $student->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);
        }

        $studentData = [
            'name' => $student->full_name,
            'photo_path' => $student->photo_path,
            'time' => $attendance->check_in_at->format('H:i'),
        ];

        // Broadcast the real-time event to the frontend
        broadcast(new StudentScanned($lecture->id, $studentData));

        return response()->json([
            'message' => 'Attendance recorded successfully.',
            'student' => $studentData,
        ], 200); // Green Alert case
    }
}
