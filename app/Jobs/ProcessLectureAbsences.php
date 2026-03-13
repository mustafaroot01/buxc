<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Warning;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessLectureAbsences implements ShouldQueue
{
    use Queueable;

    public $lecture;

    /**
     * Create a new job instance.
     */
    public function __construct(Lecture $lecture)
    {
        $this->lecture = $lecture;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lecture = $this->lecture;

        // Fetch configured threshold (default to 5)
        $thresholdSetting = Setting::where('key', 'attendance_warning_threshold')->first();
        $threshold = $thresholdSetting ? (int) $thresholdSetting->value : 5;

        // 1. Fetch ALL students in the group
        $students = Student::where('group_id', $lecture->group_id)->get();
        if ($students->isEmpty()) {
            return;
        }

        // 2. Fetch ALL existing attendance records for this lecture (including trashed)
        $existingAttendances = Attendance::withTrashed()
            ->where('lecture_id', $lecture->id)
            ->get()
            ->keyBy('student_id');

        $attendanceDataToUpsert = [];
        $studentsToIncrementStreak = [];
        $warningsToCreate = [];
        $now = Carbon::now();

        foreach ($students as $student) {
            $existing = $existingAttendances->get($student->id);

            // Logic: If NO record exists, or record exists but is NOT 'absent' or is 'trashed'
            if (! $existing || $existing->status !== 'absent' || $existing->trashed()) {
                
                // Prepare attendance data for upsert
                $attendanceDataToUpsert[] = [
                    'id'              => $existing ? $existing->id : (string) \Illuminate\Support\Str::uuid(),
                    'lecture_id'      => $lecture->id,
                    'student_id'      => $student->id,
                    'status'          => 'absent',
                    'check_in_at'     => null,
                    'check_in_method' => null,
                    'deleted_at'      => null,
                    'created_at'      => $existing ? $existing->created_at : $now,
                    'updated_at'      => $now,
                ];

                // If it's a completely NEW record or being restored/changed to 'absent', increment streak
                if (! $existing || ($existing->status !== 'absent' || $existing->trashed())) {
                    $studentsToIncrementStreak[] = $student->id;
                    
                    // Predict the new streak for warning logic
                    $newStreak = $student->consecutive_absences + 1;
                    
                    if ($newStreak > 0 && $newStreak % $threshold === 0) {
                        $newLevel = (int) ($newStreak / $threshold);
                        
                        // Check for existing active warning (still a query, but only for those hitting milestone)
                        $alreadyHasThisLevel = Warning::where('student_id', $student->id)
                            ->where('level', $newLevel)
                            ->whereNull('resolved_at')
                            ->exists();

                        if (! $alreadyHasThisLevel) {
                            $warningsToCreate[] = [
                                'id'         => (string) \Illuminate\Support\Str::uuid(),
                                'student_id' => $student->id,
                                'lecture_id' => $lecture->id,
                                'level'      => $newLevel,
                                'reason'     => "تجاوز الحد المسموح من الغيابات المتتالية ({$newStreak} غيابات).",
                                'issued_at'  => $now,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }
            }
        }

        // 3. Execution Phase: Using Database Transaction for safety
        \Illuminate\Support\Facades\DB::transaction(function () use ($attendanceDataToUpsert, $studentsToIncrementStreak, $warningsToCreate) {
            
            // Bulk Upsert Attendances
            if (!empty($attendanceDataToUpsert)) {
                Attendance::upsert($attendanceDataToUpsert, ['id'], ['status', 'check_in_at', 'check_in_method', 'deleted_at', 'updated_at']);
            }

            // Bulk Increment Streaks
            if (!empty($studentsToIncrementStreak)) {
                Student::whereIn('id', $studentsToIncrementStreak)->increment('consecutive_absences');
            }

            // Bulk Insert Warnings
            if (!empty($warningsToCreate)) {
                Warning::insert($warningsToCreate);
            }
        });
    }
}
