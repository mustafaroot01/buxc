<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Lecture;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class LectureSyncController extends Controller
{
    use ApiResponse;

    /**
     * Get updated or deleted lectures for the teacher since a given version.
     */
    public function index(Request $request)
    {
        $teacher = $request->user();
        $sinceVersion = $request->query('since_version', 0);

        // Fetch lectures that have changed (created, updated, or soft deleted)
        // only for this teacher
        $lectures = Lecture::withTrashed()
            ->with(['subject', 'group.stage'])
            ->where(function ($query) use ($teacher) {
                $query->where('teacher_id', '=', $teacher->id);
            })
            ->where(function ($query) use ($sinceVersion) {
                $query->where('version', '>', $sinceVersion);
            })
            ->orderBy('version', 'asc')
            ->limit(500)
            ->get();

        // Separate active and deleted
        $activeLectures = $lectures->whereNull('deleted_at')->map(function ($lecture) {
            return [
                'id' => $lecture->id,
                'title' => $lecture->title,
                'subject_id' => $lecture->subject_id,
                'subject_name' => $lecture->subject->name ?? 'N/A',
                'group_id' => $lecture->group_id,
                'group_name' => $lecture->group->name ?? 'N/A',
                'stage_name' => $lecture->group->stage->name ?? 'N/A',
                'start_time' => $lecture->start_time->toIso8601String(),
                'status' => $lecture->status,
                'version' => $lecture->version,
            ];
        })->values();

        $deletedIds = $lectures->whereNotNull('deleted_at')->pluck('id')->values();

        $newSyncVersion = $lectures->max('version') ?? $sinceVersion;

        return $this->success([
            'lectures' => $activeLectures,
            'deleted_ids' => $deletedIds,
            'sync_version' => $newSyncVersion,
            'server_time' => Carbon::now()->toIso8601String(),
        ], 'قائمة المحاضرات المحدثة تم جلبها بنجاح.');
    }
}
