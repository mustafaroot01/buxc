<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Student;
use App\Models\User;
use App\Models\Subject;
use App\Models\Lecture;
use App\Models\Warning;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => User::role('teacher')->count(),
            'active_subjects' => Subject::count(),
            'todays_lectures' => Lecture::whereDate('start_time', $today)->count(),
            'active_warnings' => Warning::whereNull('resolved_at')->count(),
            'banned_students' => Student::where('is_banned_from_attendance', true)->count(),
        ];

        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer ? $activity->causer->full_name : 'System',
                    'created_at' => $activity->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'todayDate' => \Carbon\Carbon::now()->translatedFormat('l، d F Y'),
        ]);
    }
}

