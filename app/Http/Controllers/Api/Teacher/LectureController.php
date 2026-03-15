<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessLectureAbsences;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureController extends Controller
{
    use ApiResponse;

    /**
     * Display a paginated listing of the teacher's lectures.
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

        $lectures = $query->paginate(15);

        // Attach total students count per group to calculate absents
        $lectures->getCollection()->transform(function ($lecture) {
            $totalStudents = Student::where('group_id', $lecture->group_id)->count();
            $lecture->total_students_count = $totalStudents;
            $lecture->absent_students_count = $totalStudents - ($lecture->present_count ?? 0);

            return $lecture;
        });

        return $this->success($lectures, 'تم جلب قائمة المحاضرات بنجاح.');
    }

    /**
     * Store a newly created lecture.
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
        $start_time = Carbon::parse("$date $time");

        $lecture = Lecture::create([
            'title' => $validated['title'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => Auth::id(),
            'group_id' => $validated['group_id'],
            'start_time' => $start_time,
            'status' => 'active',
            'offline_id' => $request->offline_id, // Capture offline_id if provided
        ]);

        return $this->success(
            $lecture->load(['subject', 'group.stage']),
            'تم إنشاء المحاضرة بنجاح.',
            201
        );
    }

    /**
     * Show lecture details with students list.
     */
    public function show($id)
    {
        // Try resolving by UUID first, then by offline_id
        $lecture = Lecture::with(['subject', 'group.stage'])
            ->where('id', $id)
            ->orWhere('offline_id', $id)
            ->firstOrFail();

        if ($lecture->teacher_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $students = Student::where('group_id', $lecture->group_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'second_name', 'last_name', 'student_external_id', 'photo_path']);

        $attendances = Attendance::where('lecture_id', $lecture->id)
            ->get()
            ->keyBy('student_id');

        $studentsWithStatus = $students->map(function ($student) use ($attendances) {
            $attendance = $attendances->get($student->id);

            return [
                'id' => $student->id,
                'full_name' => trim("{$student->first_name} {$student->second_name} {$student->last_name}"),
                'external_id' => $student->student_external_id,
                'photo_url' => $student->photo_path ? asset('storage/'.$student->photo_path) : null,
                'status' => $attendance ? $attendance->status : 'absent',
                'check_in_method' => $attendance ? $attendance->check_in_method : null,
                'check_in_at' => $attendance ? $attendance->check_in_at : null,
            ];
        });

        return $this->success([
            'lecture' => $lecture,
            'students' => $studentsWithStatus,
            'summary' => [
                'total_students' => $students->count(),
                'present_count' => $attendances->where('status', 'present')->count(),
                'absent_count' => $students->count() - $attendances->where('status', 'present')->count(),
            ],
        ], 'تم جلب تفاصيل المحاضرة بنجاح.');
    }

    /**
     * Toggle manual attendance for a student.
     */
    public function toggleAttendance(Request $request, $lectureId)
    {
        // Resolve lecture by UUID or offline_id
        $lecture = Lecture::where('id', $lectureId)
            ->orWhere('offline_id', $lectureId)
            ->firstOrFail();

        if ($lecture->teacher_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }



        $request->validate(['student_id' => 'required|exists:students,id']);
        $studentId = $request->student_id;

        $existing = Attendance::withTrashed()
            ->where('lecture_id', $lectureId)
            ->where('student_id', $studentId)
            ->first();

        // If it exists and is 'present' (not trashed), we toggle to 'absent'
        if ($existing && $existing->status === 'present' && ! $existing->trashed()) {
            $existing->delete(); // This performs soft delete

            return $this->success(['status' => 'absent'], 'تم إلغاء تسجيل الحضور بنجاح.');
        }

        // Otherwise (it's either trashed, has 'absent' status, or doesn't exist), we make it 'present'
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

        return $this->success([
            'status' => 'present',
            'check_in_at' => $attendance->check_in_at,
        ], 'تم تسجيل الحضور بنجاح.');
    }

    /**
     * Update lecture status (e.g. close session).
     */
    public function update(Request $request, $id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:active,closed',
        ]);

        $lecture->update(['status' => $validated['status']]);

        // If lecture is closed, process absences in the background
        if ($validated['status'] === 'closed') {
            \App\Jobs\ProcessLectureAbsences::dispatch($lecture);
        }

        return $this->success($lecture, 'تم تحديث حالة المحاضرة ويجري معالجة الغيابات في الخلفية.');
    }

    /**
     * Remove the specified lecture from storage (Soft Delete).
     */
    public function destroy($id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== Auth::id()) {
            return $this->error('غير مصرح لك بحذف هذه المحاضرة.', 403);
        }

        $lecture->delete();

        return $this->success(null, 'تم حذف المحاضرة بنجاح.');
    }

    /**
     * Export attendance report for a specific lecture (API).
     */
    public function export($id)
    {
        $lecture = Lecture::findOrFail($id);

        if ($lecture->teacher_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $date = \Carbon\Carbon::parse($lecture->start_time)->format('Y_m_d');
        $safeName = preg_replace('/[^\p{Arabic}\w\s]/u', '', $lecture->title);
        $fileName = "كشف_حضور_{$safeName}_{$date}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LectureAttendanceExport($id), 
            $fileName
        );
    }
}
