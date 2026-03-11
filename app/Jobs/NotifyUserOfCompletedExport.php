<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ExportCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUserOfCompletedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;

    public $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $fileName)
    {
        $this->userId = $userId;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new ExportCompletedNotification($this->fileName));
        }
    }
}
