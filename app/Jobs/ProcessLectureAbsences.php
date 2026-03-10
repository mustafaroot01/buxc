<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Warning;
use App\Models\Setting;
use Carbon\Carbon;

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
        
        // Find all students that belong to this lecture's target audience
        $students = Student::where('stage_id', $lecture->stage_id)
            ->where('group_id', $lecture->group_id)
            ->where('study_type', $lecture->study_type)
            ->get();

        // Fetch configured threshold (default to 5)
        $thresholdSetting = Setting::where('key', 'attendance_warning_threshold')->first();
        $threshold = $thresholdSetting ? (int) $thresholdSetting->value : 5;

        foreach ($students as $student) {
            // Check if they already have an attendance record for this lecture (present, excused, etc.)
            $attended = Attendance::where('lecture_id', $lecture->id)
                ->where('student_id', $student->id)
                ->exists();

            if (!$attended) {
                // Mark them absent
                Attendance::create([
                    'lecture_id' => $lecture->id,
                    'student_id' => $student->id,
                    'status' => 'absent',
                    'check_in_at' => null,
                    'check_in_method' => null,
                ]);

                // Increment their consecutive absences streak
                $student->increment('consecutive_absences');

                // If they reached or exceeded the threshold, assign a warning (if they don't have an active one)
                if ($student->consecutive_absences >= $threshold) {
                    $hasActiveWarning = Warning::where('student_id', $student->id)
                        ->whereNull('resolved_at')
                        ->exists();

                    if (!$hasActiveWarning) {
                        // Determine the level based on previous un-resolved or historically resolved warnings
                        $previousWarningsCount = Warning::where('student_id', $student->id)->count();
                        $newLevel = $previousWarningsCount + 1;

                        Warning::create([
                            'student_id' => $student->id,
                            'lecture_id' => $lecture->id,
                            'level' => $newLevel,
                            'reason' => "تجاوز الحد المسموح من الغيابات المتتالية ({$threshold} غيابات).",
                            'issued_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }
    }
}

