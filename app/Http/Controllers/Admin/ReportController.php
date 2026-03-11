<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        // Cache static academic structure (stages, groups, subjects) for 24 hours
        $stages = \Illuminate\Support\Facades\Cache::remember('admin_reports_academic_structure', 60 * 24, function () {
            return \App\Models\AcademicStage::with(['groups', 'subjects'])->get();
        });

        return Inertia::render('Admin/Reports/Index', [
            'stages' => $stages,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'stage_id' => 'nullable|exists:academic_stages,id',
            'study_type' => 'nullable|in:morning,evening',
            'subject_id' => 'nullable|exists:subjects,id',
            'group_id' => 'nullable|exists:academic_groups,id',
        ]);

        $date = Carbon::now()->format('Y-m-d_H-i');
        $fileName = "attendance_report_{$date}.xlsx";

        // Queue the export in the background; notify the user when done
        Excel::queue(new AttendanceExport($filters), "exports/{$fileName}")->chain([
            new \App\Jobs\NotifyUserOfCompletedExport(auth()->id(), $fileName),
        ]);

        return back()->with('success', 'جاري تجهيز التقرير في الخلفية، سيظهر رابط التحميل فور الاكتمال.');
    }

    public function downloadExport($file)
    {
        $filePath = storage_path('app/private/exports/'.$file);

        // Fallback for different Laravel versions using standard 'app' directory structure
        if (! file_exists($filePath)) {
            $filePath = storage_path('app/exports/'.$file);
        }

        if (! file_exists($filePath)) {
            abort(404, 'الملف غير موجود أو قيد التجهيز.');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
