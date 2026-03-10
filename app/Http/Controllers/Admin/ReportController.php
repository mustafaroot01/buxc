<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lecture;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $stages = \App\Models\AcademicStage::with(['groups', 'subjects'])->get();
        
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

        $date = Carbon::now()->format('Y_m_d_H_i');
        $fileName = "attendance_report_{$date}.xlsx";
        
        return Excel::download(new AttendanceExport($filters), $fileName);
    }
}

