<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warning;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    /**
     * Display a listing of active warnings for the teacher's students.
     */
    public function index()
    {
        $user = Auth::user();

        // Find group IDs related to this teacher
        $myGroupIds = Subject::with(['groups'])
            ->where('teacher_id', $user->id)
            ->get()
            ->flatMap(fn ($s) => $s->groups)
            ->pluck('id')
            ->unique();

        $warnings = Warning::with(['student.group.stage'])
            ->whereIn('student_id', function ($query) use ($myGroupIds) {
                $query->select('id')->from('students')->whereIn('group_id', $myGroupIds);
            })
            ->whereNull('resolved_at')
            ->orderBy('issued_at', 'desc')
            ->paginate(15);

        // Map for cleaner API response
        $warnings->getCollection()->transform(function ($warning) {
            return [
                'id' => $warning->id,
                'student_id' => $warning->student_id,
                'student_name' => $warning->student->full_name,
                'external_id' => $warning->student->student_external_id,
                'stage' => $warning->student->group->stage->name ?? 'N/A',
                'group' => $warning->student->group->name ?? 'N/A',
                'warning_level' => $warning->warning_level,
                'reason' => $warning->reason,
                'issued_at' => $warning->issued_at,
                'issued_at_human' => $warning->issued_at->diffForHumans(),
            ];
        });

        return response()->json($warnings);
    }
}
