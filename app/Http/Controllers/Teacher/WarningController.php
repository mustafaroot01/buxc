<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Subject;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();

        // Get the groups assigned to this teacher across all their subjects
        $groupIds = Subject::where('teacher_id', $teacher->id)
            ->with('groups')
            ->get()
            ->pluck('groups')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->toArray();

        // Ensure we only fetch warnings for students in those groups
        $studentsQuery = Student::whereIn('group_id', $groupIds)
            ->has('warnings')
            ->with(['group.stage', 'warnings' => function ($query) {
                // Fetch warnings, ordered by latest
                $query->latest('issued_at');
            }]);

        // Basic search by student name
        if ($request->filled('search')) {
            $studentsQuery->where('full_name', 'like', '%' . $request->search . '%');
        }

        $students = $studentsQuery->paginate(15)->withQueryString()->through(function ($student) {
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

        return Inertia::render('Teacher/Warnings/Index', [
            'warnings' => $students,
            'filters' => $request->only('search'),
        ]);
    }
}
