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

        // We try to run supervisorctl. 
        // Note: This requires the web user to have sudo access for supervisorctl without password.
        $result = shell_exec("sudo supervisorctl {$action} {$service} 2>&1");

        return back()->with('success', "تم تنفيذ العملية بنجاح: {$result}");
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
