<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\Subject;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Get this teacher's subjects with all their assigned groups
        $mySubjects = Subject::with(['stage', 'groups'])
            ->where('teacher_id', $user->id)
            ->get();

        // Count total students across all my groups
        $myGroupIds = $mySubjects->flatMap(fn ($s) => $s->groups)->pluck('id')->unique();
        $totalStudents = \App\Models\Student::whereIn('group_id', $myGroupIds)->count();

        $stats = [
            'my_subjects' => $mySubjects->count(),
            'todays_lectures' => Lecture::where('teacher_id', $user->id)->whereDate('start_time', $today)->count(),
            'total_lectures_given' => Lecture::where('teacher_id', $user->id)->where('status', 'closed')->count(),
            'total_students' => $totalStudents,
        ];

        $activeLectures = Lecture::with(['subject', 'group', 'attendances'])
            ->where('teacher_id', $user->id)
            ->whereDate('start_time', $today)
            ->where('status', 'active')
            ->orderBy('start_time', 'asc')
            ->get();

        // Fetch recent active warnings for the teacher's students
        $recentWarnings = \App\Models\Warning::with(['student.group.stage'])
            ->whereIn('student_id', function ($query) use ($myGroupIds) {
                $query->select('id')->from('students')->whereIn('group_id', $myGroupIds);
            })
            ->whereNull('resolved_at')
            ->orderBy('issued_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($warning) {
                return [
                    'id' => $warning->id,
                    'student_name' => $warning->student->full_name,
                    'stage_name' => $warning->student->group->stage->name ?? 'N/A',
                    'group_name' => $warning->student->group->name ?? 'N/A',
                    'issued_at' => $warning->issued_at->diffForHumans(),
                ];
            });

        return Inertia::render('Teacher/Dashboard', [
            'stats' => $stats,
            'activeLectures' => $activeLectures,
            'mySubjects' => $mySubjects,
            'recentWarnings' => $recentWarnings,
        ]);
    }
}
