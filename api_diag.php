<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lecture;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

function findNonUtf8($data, $path = '') {
    if (is_string($data)) {
        if (!mb_check_encoding($data, 'UTF-8')) {
            echo "INVALID UTF-8 at {$path}: " . bin2hex($data) . "\n";
            return true;
        }
    } elseif (is_array($data) || is_object($data)) {
        foreach ($data as $key => $value) {
            if (findNonUtf8($value, $path . '.' . $key)) return true;
        }
    }
    return false;
}

$targetEmail = $argv[1] ?? 'test@test.com';
$user = User::where('email', $targetEmail)->first();

if (!$user) {
    die("User with email {$targetEmail} not found.\n");
}

Auth::login($user);
$today = Carbon::today();

echo "Checking API Dashboard Data for user: {$user->full_name} ({$user->id})...\n";

$mySubjects = \App\Models\Subject::with(['groups'])->where('teacher_id', $user->id)->get();
$myGroupIds = $mySubjects->flatMap(fn ($s) => $s->groups)->pluck('id')->unique();
$totalStudents = \App\Models\Student::whereIn('group_id', $myGroupIds)->count();

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
            'issued_at_human' => $warning->issued_at->diffForHumans(),
        ];
    });

$data = [
    'user' => $user->toArray(),
    'stats' => $stats,
    'active_lectures' => $activeLectures->toArray(),
    'recent_warnings' => $recentWarnings->toArray(),
];

if (findNonUtf8($data)) {
    echo "Found malformed data!\n";
} else {
    echo "No malformed data found in full response array.\n";
    
    echo "Attempting json_encode...\n";
    $json = json_encode($data);
    if ($json === false) {
        echo "json_encode failed: " . json_last_error_msg() . "\n";
    } else {
        echo "json_encode succeeded!\n";
    }
}
