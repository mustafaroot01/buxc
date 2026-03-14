<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AttendanceSyncLog;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceSyncLog::with(['lecture.subject']);

        $logs = $query->latest()
            ->paginate(20)
            ->withQueryString();

        // Calculate statistics for the dashboard
        $stats = [
            'total_scans_today' => AttendanceSyncLog::whereDate('synced_at', '=', Carbon::today(), 'and')->sum('scans_processed'),
            'failed_syncs_today' => AttendanceSyncLog::whereDate('synced_at', '=', Carbon::today(), 'and')
                ->where(function ($query) {
                    $query->where('status', '=', 'failed', 'and')
                        ->orWhere('status', '=', 'partial', 'and');
                }, null, null, 'and')->count('id'),
            'avg_duration_ms' => round(AttendanceSyncLog::whereDate('synced_at', '=', Carbon::today(), 'and')->avg('duration_ms') ?? 0),
            'total_devices' => AttendanceSyncLog::distinct('device_id')->count('device_id'),
        ];

        return Inertia::render('Admin/System/SyncLogs', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }

    public function errors()
    {
        $logs = \App\Models\ApiErrorLog::with(['user'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/System/ApiErrors', [
            'logs' => $logs
        ]);
    }
    public function clearErrors()
    {
        \App\Models\ApiErrorLog::truncate();
        return back()->with('success', 'تم تصفير سجل أخطاء الـ API بنجاح.');
    }
}
