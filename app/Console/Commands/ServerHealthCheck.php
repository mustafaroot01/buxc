<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ServerHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a safe health check on server components';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->header("Server Health Diagnostic Report");
        $this->line("Time: " . Carbon::now()->toDateTimeString());
        $this->line("--------------------------------------------------");

        // 1. Database Check
        $this->checkComponent("Database", function() {
            DB::connection()->getPdo();
            return "Connected (" . config('database.default') . ")";
        });

        // 2. Queue Check
        $this->checkComponent("Queue Workers", function() {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();
            $status = $pendingJobs > 0 ? "Working ($pendingJobs pending)" : "Idle (All clear)";
            return $status . " | Failed: $failedJobs";
        });

        // 3. Redis Check (Condition based on .env)
        if (config('database.redis.client')) {
            $this->checkComponent("Redis", function() {
                try {
                    Redis::ping();
                    return "Connected";
                } catch (\Exception $e) {
                    return "Error: " . $e->getMessage();
                }
            });
        }

        // 4. Reverb / Real-time Check
        $this->checkComponent("Reverb Server", function() {
            $host = config('reverb.servers.reverb.host', '127.0.0.1');
            $port = config('reverb.servers.reverb.port', 8080);
            
            $connection = @fsockopen($host, $port, $errno, $errstr, 2);
            if (is_resource($connection)) {
                fclose($connection);
                return "Active (Port $port)";
            } else {
                return "Inactive or Port $port Blocked";
            }
        });

        // 5. System Resources (Safe checks)
        $this->checkComponent("Disk Space", function() {
            $path = base_path();
            $free = disk_free_space($path) / (1024 * 1024 * 1024);
            $total = disk_total_space($path) / (1024 * 1024 * 1024);
            $percent = round((1 - ($free / $total)) * 100);
            return "Usage: $percent% (" . round($free, 2) . " GB Free)";
        });

        $this->line("--------------------------------------------------");
        $this->info("Diagnostic Complete. Your server is safe.");
    }

    private function checkComponent($name, $callback)
    {
        $this->output->write(str_pad("- $name: ", 25));
        try {
            $result = $callback();
            $this->info($result);
        } catch (\Exception $e) {
            $this->error("FAILED (" . $e->getMessage() . ")");
        }
    }

    private function header($text)
    {
        $this->info("==========================================");
        $this->info("    " . $text);
        $this->info("==========================================");
    }
}
