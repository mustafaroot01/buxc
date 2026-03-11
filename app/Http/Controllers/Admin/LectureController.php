<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicGroup;
use App\Models\AcademicStage;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LectureController extends Controller
{
    public function index(Request $request)
    {
        $query = Lecture::with(['subject', 'group.stage', 'teacher'])
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('status', 'present');
                },
            ])
            ->latest('start_time');

        // ── Filters ────────────────────────────────────────────────────────
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhereHas('teacher', fn ($t) => $t->where('full_name', 'like', '%'.$request->search.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('stage_id')) {
            $query->whereHas('group', fn ($g) => $g->where('stage_id', $request->stage_id));
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('study_type')) {
            $query->whereHas('group', fn ($g) => $g->where('study_type', $request->study_type));
        }

        $lectures = $query->paginate(20)->withQueryString();

        // Attach total & absent count per lecture
        $lectures->getCollection()->transform(function ($lecture) {
            $total                  = Student::where('group_id', $lecture->group_id)->count();
            $lecture->total_students = $total;
            $lecture->absent_count   = $total - ($lecture->present_count ?? 0);
            return $lecture;
        });

        // Stats
        $stats = [
            'total'  => Lecture::count(),
            'active' => Lecture::where('status', 'active')->count(),
            'closed' => Lecture::where('status', 'closed')->count(),
        ];

        return Inertia::render('Admin/Lectures/Index', [
            'lectures' => $lectures,
            'stats'    => $stats,
            'teachers' => User::role('teacher')->get(['id', 'full_name']),
            'stages'   => AcademicStage::all(['id', 'name']),
            'groups'   => AcademicGroup::all(['id', 'name', 'stage_id', 'study_type']),
            'filters'  => $request->only('search', 'status', 'teacher_id', 'stage_id', 'group_id', 'study_type'),
        ]);
    }

    public function export($id)
    {
        $lecture = Lecture::with(['subject', 'group.stage', 'teacher'])->findOrFail($id);
        
        $date = $lecture->start_time->format('Y-m-d');
        $fileName = "attendance_{$lecture->subject->name}_{$date}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LectureAttendanceExport($id), 
            $fileName
        );
    }
}
