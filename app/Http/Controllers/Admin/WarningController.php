<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Inertia\Inertia;
use Carbon\Carbon;

class WarningController extends Controller
{
    public function index()
    {
        $students = Student::has('warnings')
            ->with(['group.stage', 'warnings' => function ($query) {
                $query->latest('issued_at');
            }])
            ->paginate(15)
            ->through(function ($student) {
                $activeWarningsCount = $student->warnings->whereNull('resolved_at')->count();
                $totalWarningsCount = $student->warnings->count();
                $lastWarningDate = $student->warnings->first() ? $student->warnings->first()->issued_at->format('Y-m-d H:i') : 'N/A';

                return [
                    'id' => $student->id,
                    'student_name' => $student->full_name,
                    'student_external_id' => $student->student_external_id,
                    'stage_name' => $student->group && $student->group->stage ? $student->group->stage->name : 'N/A',
                    'group_name' => $student->group ? $student->group->name : 'N/A',
                    'active_warnings' => $activeWarningsCount,
                    'total_warnings' => $totalWarningsCount,
                    'consecutive_absences' => $student->consecutive_absences,
                    'last_warning_date' => $lastWarningDate,
                ];
            });

        return Inertia::render('Admin/Warnings/Index', [
            'warnings' => $students
        ]);
    }

    public function export()
    {
        $students = Student::has('warnings')
            ->with(['group.stage', 'warnings' => function ($query) {
                $query->latest('issued_at');
            }])
            ->get();

        $filename = "student_warnings_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'اسم الطالب', 
            'الرقم الجامعي', 
            'المرحلة', 
            'المجموعة', 
            'الغياب المتتالي الحالي', 
            'إجمالي الإنذارات', 
            'الإنذارات النشطة', 
            'تاريخ آخر إنذار'
        ];

        $callback = function() use($students, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper Arabic rendering in Excel
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, $columns);

            foreach ($students as $student) {
                $activeWarningsCount = $student->warnings->whereNull('resolved_at')->count();
                $totalWarningsCount = $student->warnings->count();
                $lastWarningDate = $student->warnings->first() ? $student->warnings->first()->issued_at->format('Y-m-d') : 'N/A';

                fputcsv($file, [
                    $student->full_name,
                    $student->student_external_id,
                    $student->group && $student->group->stage ? $student->group->stage->name : 'N/A',
                    $student->group ? $student->group->name : 'N/A',
                    $student->consecutive_absences,
                    $totalWarningsCount,
                    $activeWarningsCount,
                    $lastWarningDate
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
