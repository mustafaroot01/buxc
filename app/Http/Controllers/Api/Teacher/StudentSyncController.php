<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Student;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class StudentSyncController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $teacher = $request->user();
        $sinceVersion = $request->query('since_version', 0);
        $all = $request->boolean('all', false);
        $perPage = (int) $request->query('per_page', 1000);
        
        // Safety cap for per_page
        if ($perPage > 5000) $perPage = 5000;

        // 1. Get all group IDs that the teacher teaches (through subjects)
        $groupIds = $teacher->subjects()
            ->with('groups')
            ->get()
            ->pluck('groups')
            ->flatten()
            ->pluck('id')
            ->unique()
            ->values()
            ->toArray();

        // 2. Query students in those groups
        $query = Student::withTrashed()
            ->with(['group.stage'])
            ->whereIn('group_id', $groupIds);

        // If 'all' is not set, only get students changed since since_version
        if (!$all) {
            $query->where('version', '>', $sinceVersion);
        }

        $students = $query->orderBy('version', 'asc')
            ->limit($perPage)
            ->get();

        // 3. Separate into active and deleted
        $activeStudents = $students->whereNull('deleted_at')->map(function ($student) {
            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'student_external_id' => $student->student_external_id,
                'qr_hash' => hash('sha256', (string)$student->qr_payload), // App matches against hash
                'group_name' => $student->group->name ?? 'N/A',
                'stage_name' => $student->group->stage->name ?? 'N/A',
                'study_type' => $student->group->study_type ?? 'N/A',
                'version' => $student->version,
                'is_banned' => (bool) $student->is_banned_from_attendance,
            ];
        })->values();

        $deletedIds = $students->whereNotNull('deleted_at')->pluck('id')->values();

        // 4. Get the new max version to send back (or keep sinceVersion if no updates)
        $newSyncVersion = $students->max('version') ?? $sinceVersion;

        return $this->success([
            'students' => $activeStudents,
            'deleted_ids' => $deletedIds,
            'sync_version' => $newSyncVersion,
            'server_time' => Carbon::now()->toIso8601String(),
            'total_count' => $students->count(),
        ], 'قائمة الطلاب المحدثة تم جلبها بنجاح.');
    }
}
