<?php

namespace App\Http\Controllers\Teacher;

use App\Exports\LectureAttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicStage;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class LectureController extends Controller
{
    /**
     * Display a listing of the teacher's lectures.
     */
    public function index(Request $request)
    {
        $teacherId = Auth::id();

        $query = Lecture::with(['subject', 'group.stage'])
            ->withCount([
                'attendances as present_count' => function ($q) {
                    $q->where('status', 'present');
                },
            ])
            ->where('teacher_id', $teacherId)
            ->latest('start_time');

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('stage_id')) {
            $query->whereHas('group', function ($q) use ($request) {
                $q->where('stage_id', $request->stage_id);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lectures = $query->paginate(15)->withQueryString();

        // Attach total students count per group to calculate absents
        $lectures->getCollection()->transform(function ($lecture) {
            $totalStudents = Student::where('group_id', $lecture->group_id)->count();
            $lecture->total_students = $totalStudents;
            $lecture->absent_count = $totalStudents - ($lecture->present_count ?? 0);

            return $lecture;
        });

        $version = \Illuminate\Support\Facades\Cache::get('academic_structure_version', 1);

        // Get unique subjects the teacher has taught or is assigned to for filtering
        $subjects = \Illuminate\Support\Facades\Cache::remember("teacher_{$teacherId}_subjects_list_v{$version}", 60 * 24, function () use ($teacherId) {
            return Subject::where('teacher_id', $teacherId)->get(['id', 'name']);
        });

        // Get unique stages the teacher is teaching based on groups of their subjects
        $stages = \Illuminate\Support\Facades\Cache::remember("teacher_{$teacherId}_stages_list_v{$version}", 60 * 24, function () use ($teacherId) {
            return AcademicStage::whereHas('groups', function ($q) use ($teacherId) {
                $q->whereHas('subjects', function ($q2) use ($teacherId) {
                    $q2->where('teacher_id', $teacherId);
                });
            })->get(['id', 'name']);
        });

        return Inertia::render('Teacher/Lectures/Index', [
            'lectures' => $lectures,
            'subjects' => $subjects,
            'stages' => $stages,
            'filters' => $request->only('search', 'subject_id', 'stage_id', 'status'),
        ]);
    }

    /**
     * Show the form for creating a new lecture.
     */
    public function create()
    {
        $teacherId = Auth::id();

        // Get subjects assigned to this teacher with their specifically assigned groups
        $version = \Illuminate\Support\Facades\Cache::get('academic_structure_version', 1);
        $subjects = \Illuminate\Support\Facades\Cache::remember("teacher_{$teacherId}_subjects_structure_v{$version}", 60 * 24, function () use ($teacherId) {
            return Subject::with(['stage', 'groups'])
                ->where('teacher_id', $teacherId)
                ->get();
        });

        return Inertia::render('Teacher/Lectures/Create', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * Store a newly created lecture in storage and redirect to scanner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'stage_id' => 'required|exists:academic_stages,id',
            'group_id' => 'required|exists:academic_groups,id',
            'study_type' => 'required|in:morning,evening',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $time = $validated['time'] ?? now()->format('H:i');
        $start_time = \Carbon\Carbon::parse("$date $time");

        $lecture = Lecture::create([
            'title' => $validated['title'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => Auth::id(),
            'group_id' => $validated['group_id'],
            'start_time' => $start_time,
            'status' => 'active',
        ]);

        // Redirect directly to the scanner page for this new lecture
        return redirect()->route('teacher.scanner.show', $lecture->id)
            ->with('success', 'تم إنشاء المحاضرة بنجاح. أداة المسح جاهزة الآن.');
    }

    /**
     * Show lecture details with all students attendance status.
     */
    public function show($id, Request $request)
    {
        $lecture = Lecture::with(['subject', 'group.stage', 'teacher'])->findOrFail($id);

        // Only the assigned teacher can view this
        if ($lecture->teacher_id !== Auth::id()) {
            abort(403);
        }

        // Get all students in this group
        $studentsQuery = Student::where('group_id', $lecture->group_id);

        // --- Improved Student Search in Lecture Details ---
        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->whereRaw("CONCAT(first_name, ' ', COALESCE(second_name, ''), ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('student_external_id', 'like', "%{$search}%");
            });
        }

        $students = $studentsQuery->orderBy('first_name')
            ->get(['id', 'first_name', 'second_name', 'last_name', 'student_external_id']);

        // Get existing attendance records for this lecture
        $attendances = Attendance::where('lecture_id', $lecture->id)
            ->get()
            ->keyBy('student_id');

        // Merge: mark each student as present or absent
        $studentsWithStatus = $students->map(function ($student) use ($attendances) {
            $attendance = $attendances->get($student->id);

            return [
                'id' => $student->id,
                'name' => trim("{$student->first_name} {$student->second_name} {$student->last_name}"),
                'student_id' => $student->student_external_id,
                'status' => $attendance ? $attendance->status : 'absent',
                'check_in_method' => $attendance ? $attendance->check_in_method : null,
                'check_in_at' => $attendance ? $attendance->check_in_at : null,
                'attendance_id' => $attendance ? $attendance->id : null,
            ];
        });

        // Summary should represent all students in the group for consistency
        $totalInGroup = Student::where('group_id', $lecture->group_id)->count();
        $presentCount = Attendance::where('lecture_id', $lecture->id)->where('status', 'present')->count();

        $summary = [
            'total' => $totalInGroup,
            'present' => $presentCount,
            'absent' => $totalInGroup - $presentCount,
        ];

        return Inertia::render('Teacher/Lectures/Show', [
            'lecture' => $lecture,
            'students' => $studentsWithStatus,
            'summary' => $summary,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Toggle manual attendance for a student.
     */
    public function markManual(Request $request, $lectureId)
    {
        $lecture = Lecture::findOrFail($lectureId);

        if ($lecture->teacher_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك.'], 403);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $studentId = $request->student_id;

        // Check if student belongs to this lecture's group
        $student = Student::findOrFail($studentId);
        if ($student->group_id !== $lecture->group_id) {
            return response()->json(['success' => false, 'message' => 'الطالب لا ينتمي لهذه المجموعة.'], 400);
        }

        $existing = Attendance::withTrashed()
            ->where('lecture_id', $lectureId)
            ->where('student_id', $studentId)
            ->first();

        if ($existing && $existing->status === 'present' && ! $existing->trashed()) {
            // Toggle to absent
            if ($lecture->status === 'closed') {
                // Lecture is closed: update to 'absent' to maintain the record
                $existing->update([
                    'status' => 'absent',
                    'check_in_method' => null,
                    'check_in_at' => null,
                ]);
            } else {
                // Lecture still active: just delete (absence = no record)
                $existing->delete();
            }

            return response()->json(['success' => true, 'action' => 'removed', 'message' => 'تم تغيير الحالة إلى غائب.']);
        }

        // If we reach here, we want to mark as present.
        // If there's an 'absent' or trashed record, we update it. If no record, we create one.
        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update([
                'status' => 'present',
                'check_in_method' => 'manual',
                'check_in_at' => now(),
            ]);
            $attendance = $existing;
        } else {
            $attendance = Attendance::create([
                'lecture_id' => $lectureId,
                'student_id' => $studentId,
                'status' => 'present',
                'check_in_method' => 'manual',
                'check_in_at' => now(),
            ]);
        }

        // --- Logic to resolve warnings when marking present ---
        if ($student->consecutive_absences > 0) {
            $student->update(['consecutive_absences' => 0]);

            \App\Models\Warning::where('student_id', $student->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'action' => 'added',
            'attendance_id' => $attendance->id,
            'message' => 'تم تسجيل الحضور بنجاح.',
        ]);
    }

    /**
     * Export attendance report for a specific lecture.
     */
    public function export($id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== Auth::id()) {
            abort(403);
        }

        $date = \Carbon\Carbon::parse($lecture->start_time)->format('Y_m_d');
        $safeName = preg_replace('/[^\p{Arabic}\w\s]/u', '', $lecture->title);
        $fileName = "كشف_حضور_{$safeName}_{$date}.xlsx";

        return Excel::download(new LectureAttendanceExport($id), $fileName);
    }

    /**
     * Delete a lecture (soft delete). Only the owning teacher can delete.
     */
    public function destroy($id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== Auth::id()) {
            abort(403);
        }

        $lecture->delete();

        return redirect()->route('teacher.lectures.index')
            ->with('success', 'تم حذف المحاضرة بنجاح.');
    }
}
