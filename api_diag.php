<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lecture;
use App\Models\User;
use Carbon\Carbon;

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

$user = User::role('teacher')->first();
if (!$user) {
    die("No teacher found.\n");
}

Auth::login($user);
$today = Carbon::today();

echo "Checking API Dashboard Data for teacher: {$user->id}...\n";

$activeLectures = Lecture::with(['subject', 'group', 'attendances'])
    ->where('teacher_id', $user->id)
    ->whereDate('start_time', $today)
    ->where('status', 'active')
    ->orderBy('start_time', 'asc')
    ->get();

$data = [
    'active_lectures' => $activeLectures->toArray(),
];

if (findNonUtf8($data)) {
    echo "Found malformed data!\n";
} else {
    echo "No malformed data found in active_lectures (as array).\n";
    
    echo "Attempting json_encode...\n";
    $json = json_encode($data);
    if ($json === false) {
        echo "json_encode failed: " . json_last_error_msg() . "\n";
    } else {
        echo "json_encode succeeded!\n";
    }
}
