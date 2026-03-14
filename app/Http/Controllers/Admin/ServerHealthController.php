<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class ServerHealthController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/System/ServerHealth', [
            'metrics' => [
                'system' => $this->getSystemMetrics(),
                'database' => $this->getDatabaseMetrics(),
                'redis' => $this->getRedisMetrics(),
                'queue' => $this->getQueueMetrics(),
                'reverb' => $this->getReverbStatus(),
                'logs' => $this->getSystemLogsSummary(),
            ],
            'server_time' => Carbon::now()->toIso8601String(),
        ]);
    }

    private function getSystemMetrics()
    {
        $metrics = [
            'cpu_load' => 'N/A',
            'memory' => [
                'total' => 'N/A',
                'used' => 'N/A',
                'percentage' => 0,
            ],
            'disk' => [
                'total' => 'N/A',
                'free' => 'N/A',
                'usage_percentage' => 0,
            ],
            'uptime' => 'N/A',
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
        ];

        // RAM Usage (Linux)
        if (PHP_OS_FAMILY === 'Linux') {
            $free = shell_exec('free -m');
            if ($free) {
                $lines = explode("\n", trim($free));
                $mem = preg_split('/\s+/', $lines[1]);
                $metrics['memory']['total'] = $mem[1] . ' MB';
                $metrics['memory']['used'] = $mem[2] . ' MB';
                $metrics['memory']['percentage'] = round(($mem[2] / $mem[1]) * 100);
            }

            // CPU Load
            $load = sys_getloadavg();
            if ($load) {
                $metrics['cpu_load'] = $load[0] . ' (1m), ' . $load[1] . ' (5m)';
            }

            // Uptime
            $uptime = shell_exec('uptime -p');
            if ($uptime) {
                $metrics['uptime'] = trim($uptime);
            }
        }

        // Disk Usage
        $path = base_path();
        $freeSpace = disk_free_space($path);
        $totalSpace = disk_total_space($path);
        if ($freeSpace && $totalSpace) {
            $metrics['disk']['total'] = round($totalSpace / (1024 * 1024 * 1024), 2) . ' GB';
            $metrics['disk']['free'] = round($freeSpace / (1024 * 1024 * 1024), 2) . ' GB';
            $metrics['disk']['usage_percentage'] = round((1 - ($freeSpace / $totalSpace)) * 100);
        }

        return $metrics;
    }

    private function getDatabaseMetrics()
    {
        $status = 'Disconnected';
        $version = 'N/A';
        $size = 'N/A';
        $connectionCount = 'N/A';

        try {
            DB::connection()->getPdo();
            $status = 'Connected';
            
            $results = DB::select('select version() as version');
            $version = $results[0]->version ?? 'N/A';

            // Connection Count
            $connections = DB::select("SHOW STATUS WHERE `variable_name` = 'Threads_connected'");
            $connectionCount = $connections[0]->Value ?? 'N/A';

            // Database Size
            $dbName = config('database.connections.mysql.database');
            $sizeResult = DB::select("
                SELECT SUM(data_length + index_length) / 1024 / 1024 AS size 
                FROM information_schema.TABLES 
                WHERE table_schema = ?
            ", [$dbName]);
            $size = round($sizeResult[0]->size ?? 0, 2) . ' MB';

        } catch (\Exception $e) {
            $status = 'Error: ' . $e->getMessage();
        }

        return [
            'status' => $status,
            'driver' => config('database.default'),
            'version' => $version,
            'size' => $size,
            'connections' => $connectionCount,
        ];
    }

    private function getRedisMetrics()
    {
        $status = 'Disabled';
        $info = [];

        if (config('database.redis.client')) {
            try {
                $redis = Redis::connection();
                $redis->ping();
                $status = 'Connected';
                
                $rawInfo = $redis->info();
                $info = [
                    'version' => $rawInfo['redis_version'] ?? 'N/A',
                    'memory_used' => $rawInfo['used_memory_human'] ?? 'N/A',
                    'uptime_days' => $rawInfo['uptime_in_days'] ?? 'N/A',
                    'clients' => $rawInfo['connected_clients'] ?? 'N/A',
                ];
            } catch (\Exception $e) {
                $status = 'Error: ' . $e->getMessage();
            }
        }

        return [
            'status' => $status,
            'info' => $info,
        ];
    }

    private function getQueueMetrics()
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'active_workers' => $this->getActiveWorkersCount(),
        ];
    }

    private function getActiveWorkersCount()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            // Count various types of workers (queue:work, queue:listen, horizon:work)
            $output = shell_exec('ps aux | grep -E "artisan (queue:work|queue:listen|horizon:work)" | grep -v grep | wc -l');
            return (int) trim($output);
        }
        return 'N/A';
    }

    private function getReverbStatus()
    {
        $host = config('reverb.servers.reverb.host', '127.0.0.1');
        $port = config('reverb.servers.reverb.port', 8080);
        
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);
        if (is_resource($connection)) {
            fclose($connection);
            return [
                'status' => 'Active',
                'port' => $port,
            ];
        }

        return [
            'status' => 'Inactive',
            'port' => $port,
        ];
    }

    private function getSystemLogsSummary()
    {
        $path = storage_path('logs/laravel.log');
        if (File::exists($path)) {
            $lastLines = shell_exec("tail -n 20 " . escapeshellarg($path));
            return $lastLines ? trim($lastLines) : 'No recent logs.';
        }
        return 'Log file not found.';
    }
}
