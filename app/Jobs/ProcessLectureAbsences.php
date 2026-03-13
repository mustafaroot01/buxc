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

        // Find all students that belong to this lecture's group
        $students = Student::where('group_id', $lecture->group_id)
            ->get();

        // Fetch configured threshold (default to 5)
        $thresholdSetting = Setting::where('key', 'attendance_warning_threshold')->first();
        $threshold = $thresholdSetting ? (int) $thresholdSetting->value : 5;

        foreach ($students as $student) {
            // Check if they already have an attendance record for this lecture (present, excused, etc.)
            $attended = Attendance::where('lecture_id', $lecture->id)
                ->where('student_id', $student->id)
                ->exists();

            if (! $attended) {
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

                // If they reached a milestone (e.g., 5, 10, 15...), assign a new warning level
                if ($student->consecutive_absences > 0 && $student->consecutive_absences % $threshold === 0) {
                    $newLevel = (int) ($student->consecutive_absences / $threshold);

                    // Check if this specific level already exists for this student and hasn't been resolved
                    // This prevents duplicate warnings for the same milestone if the job is re-run
                    $alreadyHasThisLevel = Warning::where('student_id', $student->id)
                        ->where('level', $newLevel)
                        ->whereNull('resolved_at')
                        ->exists();

                    if (! $alreadyHasThisLevel) {
                        Warning::create([
                            'student_id' => $student->id,
                            'lecture_id' => $lecture->id,
                            'level' => $newLevel,
                            'reason' => "تجاوز الحد المسموح من الغيابات المتتالية ({$student->consecutive_absences} غيابات).",
                            'issued_at' => Carbon::now(),
                        ]);
                    }
                }
            }
        }
    }
}
