<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Student;
use App\Models\Warning;
use Illuminate\Support\Facades\DB;

class ProcessSyncPostTasks implements ShouldQueue
{
    use Queueable;

    public $studentIds;

    /**
     * Create a new job instance.
     */
    public function __construct(array $studentIds)
    {
        $this->studentIds = array_unique($studentIds);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (empty($this->studentIds)) {
            return;
        }

        DB::transaction(function () {
            // 1. Reset streaks for all students marked present in the sync
            Student::whereIn('id', $this->studentIds, 'and', false)
                ->where(function ($query) {
                    $query->where('consecutive_absences', '>', 0, 'and');
                }, null, null, 'and')
                ->update(['consecutive_absences' => 0]);

            // 2. Resolve active warnings for these students
            Warning::whereIn('student_id', $this->studentIds, 'and', false)
                ->whereNull('resolved_at', 'and', false)
                ->update(['resolved_at' => now()]);
        });
    }
}
