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
        $lectures = Lecture::with(['subject', 'group.stage'])->latest()->take(50)->get();
        return Inertia::render('Admin/Reports/Index', [
            'lectures' => $lectures,
        ]);
    }

    public function export(Request $request)
    {
        $lectureId = $request->get('lecture_id');
        $date = Carbon::now()->format('Y_m_d_H_i');
        
        $prefix = $lectureId ? "lecture_{$lectureId}" : "all";
        $fileName = "attendance_report_{$prefix}_{$date}.xlsx";
        
        return Excel::download(new AttendanceExport($lectureId), $fileName);
    }
}

