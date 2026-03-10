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
        $lectures = Lecture::with(['subject', 'group.stage'])
            ->select('id', 'subject_id', 'group_id', 'date')
            ->latest()
            ->get();
        
        return Inertia::render('Admin/Reports/Index', [
            'stages' => $stages,
            'lectures' => $lectures,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'lecture_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'stage_id' => 'nullable|string',
            'study_type' => 'nullable|string',
            'subject_id' => 'nullable|string',
            'group_id' => 'nullable|string',
        ]);

        $date = Carbon::now()->format('Y_m_d_H_i');
        $fileName = "attendance_report_{$date}.xlsx";
        
        return Excel::download(new AttendanceExport($filters), $fileName);
    }
}

