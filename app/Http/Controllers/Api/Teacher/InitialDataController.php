<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\Subject;
use App\Traits\ApiResponse;

class InitialDataController extends Controller
{
    use ApiResponse;

    public function __invoke()
    {
        $user = auth()->user();

        // 1. Teacher Profile
        $profile = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'photo_url' => $user->photo_path ? asset('storage/'.$user->photo_path) : null,
        ];

        // 2. Subjects, Stages, and Groups (Hierarchical for the dropdowns)
        $subjects = Subject::where('teacher_id', $user->id)
            ->with(['groups.stage'])
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'groups' => $subject->groups->map(function ($group) {
                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            'stage_id' => $group->stage_id,
                            'stage_name' => $group->stage->name ?? 'N/A',
                            'study_type' => $group->study_type ?? 'morning',
                        ];
                    }),
                ];
            });

        // 3. Today's Active Lectures
        $activeLectures = Lecture::with(['subject', 'group'])
            ->where('teacher_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success([
            'profile' => $profile,
            'subjects' => $subjects,
            'active_lectures' => $activeLectures,
            'server_time' => now()->toDateTimeString(),
        ], 'تم جلب بيانات البداية بنجاح.');
    }
}
