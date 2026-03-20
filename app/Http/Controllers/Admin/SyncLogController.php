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
        $query = AttendanceSyncLog::with(['lecture' => function($query) {
            $query->withTrashed()->with(['subject', 'teacher']);
        }]);

        if ($request->has('search') && $request->search != '') {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('sync_id', 'like', "%{$search}%")
                  ->orWhere('device_id', 'like', "%{$search}%")
                  ->orWhereHas('lecture', function($l) use ($search) {
                      $l->where('title', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date != '') {
            $query->whereDate('synced_at', $request->date);
        }

        $logs = $query->latest()
            ->paginate(15)
            ->withQueryString();

        // Calculate statistics for the dashboard
        $stats = [
            'total_scans_today' => AttendanceSyncLog::whereDate('synced_at', '=', Carbon::today())->sum('scans_processed'),
            'failed_syncs_today' => AttendanceSyncLog::whereDate('synced_at', '=', Carbon::today())
                ->whereIn('status', [AttendanceSyncLog::STATUS_FAILED, AttendanceSyncLog::STATUS_PARTIAL])
                ->count(),
            'avg_duration_ms' => round(AttendanceSyncLog::whereDate('synced_at', '=', Carbon::today())->avg('duration_ms') ?? 0),
            'total_devices' => AttendanceSyncLog::distinct('device_id')->count('device_id'),
        ];

        return Inertia::render('Admin/System/SyncLogs', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'date'])
        ]);
    }

    public function clearOldLogs()
    {
        // Clear all current sync logs as requested by the user
        $count = AttendanceSyncLog::count();
        AttendanceSyncLog::truncate();

        return back()->with('success', "تم تصفير سجلات المزامنة بالكامل بنجاح ({$count} سجل).");
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
