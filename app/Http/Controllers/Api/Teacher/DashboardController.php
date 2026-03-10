<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Warning;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Get teacher's subjects and group IDs
        $mySubjects = Subject::with(['groups'])->where('teacher_id', $user->id)->get();
        $myGroupIds = $mySubjects->flatMap(fn ($s) => $s->groups)->pluck('id')->unique();
        
        $totalStudents = Student::whereIn('group_id', $myGroupIds)->count();

        $stats = [
            'my_subjects_count' => $mySubjects->count(),
            'todays_lectures_count' => Lecture::where('teacher_id', $user->id)->whereDate('start_time', $today)->count(),
            'total_lectures_given' => Lecture::where('teacher_id', $user->id)->where('status', 'closed')->count(),
            'total_students_count' => $totalStudents,
        ];

        $activeLectures = Lecture::with(['subject', 'group', 'attendances'])
            ->where('teacher_id', $user->id)
            ->whereDate('start_time', $today)
            ->where('status', 'active')
            ->orderBy('start_time', 'asc')
            ->get();

        $recentWarnings = Warning::with(['student.group.stage'])
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
                    'issued_at_human' => $warning->issued_at->diffForHumans(),
                ];
            });

        return response()->json([
            'stats' => $stats,
            'active_lectures' => $activeLectures,
            'recent_warnings' => $recentWarnings,
        ]);
    }
}
