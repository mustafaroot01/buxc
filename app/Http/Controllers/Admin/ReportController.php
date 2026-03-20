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

        return Excel::download(new \App\Exports\AttendanceExport($filters), $fileName);
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'group_id' => 'required|exists:academic_groups,id',
            'study_type' => 'required|in:morning,evening',
        ]);

        Excel::import(new \App\Imports\StudentImport($request->group_id, $request->study_type), $request->file('file'));

        return back()->with('success', 'تم استيراد قائمة الطلاب بنجاح.');
    }

    public function exportStudents(Request $request)
    {
        $filters = $request->validate([
            'stage_id' => 'nullable|exists:academic_stages,id',
            'group_id' => 'nullable|exists:academic_groups,id',
            'study_type' => 'nullable|in:morning,evening',
        ]);

        $date = Carbon::now()->format('Y-m-d');
        $fileName = "students_list_{$date}.xlsx";

        return Excel::download(new \App\Exports\StudentExport($filters), $fileName);
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
