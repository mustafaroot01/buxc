<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RegenerateQrPayloads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:regenerate-payloads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate QR payloads for all students using the new shorter format.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting QR payload regeneration...');
        $students = Student::all();
        $bar = $this->output->createProgressBar(count($students));

        $bar->start();

        foreach ($students as $student) {
            $student->update([
                'qr_payload' => Str::random(24),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All QR payloads have been successfully regenerated.');
    }
}
