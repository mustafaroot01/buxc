<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    private $services = [
        'bucx-worker' => 'Queue Worker',
        'bucx-reverb' => 'Reverb (WebSockets)',
    ];

    public function index()
    {
        $statuses = $this->getSupervisorStatus();

        return Inertia::render('Admin/System/Index', [
            'services' => $statuses,
            'server_info' => [
                'php_version' => PHP_VERSION,
                'os' => PHP_OS,
                'storage_logs' => $this->getRecentLogs(),
            ]
        ]);
    }

    public function action(Request $request)
    {
        $request->validate([
            'service' => 'required|string',
            'action' => 'required|in:start,stop,restart',
        ]);

        $service = $request->service;
        $action = $request->action;

        if (!array_key_exists($service, $this->services)) {
            return back()->with('error', 'خدمة غير معروفة.');
        }

        // Use more precise targeting to avoid stopping other services
        // If it's a multi-process service (like worker), we use group:*
        // If it shows up as group:process in status, we target group:*
        $target = $service . ':*';
        
        $process = \Illuminate\Support\Facades\Process::run("sudo /usr/bin/supervisorctl {$action} {$target}");
        $result = $process->output();
        $error = $process->errorOutput();

        if (!$process->successful() || str_contains(strtolower($result . $error), 'error') || str_contains(strtolower($result . $error), 'failed')) {
            return back()->with('error', "فشل التنفيذ: " . ($result ?: $error));
        }

        return back()->with('success', "تم تنفيذ ({$action}) بنجاح: " . ($result ?: 'تم إرسال الأمر'));
    }

    private function getSupervisorStatus()
    {
        $output = shell_exec("sudo supervisorctl status 2>&1");
        $lines = explode("\n", trim($output));
        
        $statuses = [];
        foreach ($this->services as $id => $name) {
            $found = false;
            foreach ($lines as $line) {
                if (str_contains($line, $id)) {
                    $parts = preg_split('/\s+/', $line);
                    $statuses[] = [
                        'id' => $id,
                        'name' => $name,
                        'status' => $parts[1] ?? 'UNKNOWN',
                        'description' => implode(' ', array_slice($parts, 2)),
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $statuses[] = [
                    'id' => $id,
                    'name' => $name,
                    'status' => 'NOT FOUND',
                    'description' => 'الخدمة غير معرفة في Supervisor',
                ];
            }
        }

        return $statuses;
    }

    private function getRecentLogs()
    {
        $logs = [];
        $files = [
            'worker.log' => storage_path('logs/worker.log'),
            'reverb.log' => storage_path('logs/reverb.log'),
            'laravel.log' => storage_path('logs/laravel.log'),
        ];

        foreach ($files as $name => $path) {
            if (File::exists($path)) {
                $content = shell_exec("tail -n 20 " . escapeshellarg($path));
                $logs[$name] = $content ? trim($content) : 'السجل فارغ حالياً.';
            } else {
                $logs[$name] = 'الملف غير موجود.';
            }
        }

        return $logs;
    }
}
