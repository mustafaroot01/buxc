<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\AcademicGroup;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('group.stage');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_external_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('stage_id') && $request->stage_id != '') {
            $query->whereHas('group', function ($q) use ($request) {
                $q->where('stage_id', $request->stage_id);
            });
        }

        if ($request->has('group_id') && $request->group_id != '') {
            $query->where('group_id', $request->group_id);
        }

        if ($request->has('study_type') && $request->study_type != '') {
            $query->whereHas('group', function ($q) use ($request) {
                $q->where('study_type', $request->study_type);
            });
        }

        $students = $query->paginate(15)->withQueryString();

        $stages = \App\Models\AcademicStage::with('groups')->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'stages' => $stages,
            'filters' => $request->only('search', 'stage_id', 'group_id', 'study_type')
        ]);
    }

    public function create()
    {
        $stages = \App\Models\AcademicStage::with('groups')->get();
        return Inertia::render('Admin/Students/Create', [
            'stages' => $stages
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'student_external_id' => 'required|string|unique:students',
            'gender' => 'required|in:male,female',
            'stage_id' => 'required|exists:academic_stages,id',
            'group_id' => 'required|exists:academic_groups,id',
            'study_type' => 'required|in:morning,evening',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('students', 'public');
            $validated['photo_path'] = $path;
        }

        // Generate encrypted QR payload
        // The payload only contains the external ID, making it lightweight but secure
        $qrPayload = Crypt::encryptString($validated['student_external_id']);
        $validated['qr_payload'] = $qrPayload;

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully with QR code.');
    }

    public function edit(Student $student)
    {
        $student->load('group.stage');
        $stages = \App\Models\AcademicStage::with('groups')->get();
        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
            'stages' => $stages
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'student_external_id' => 'required|string|unique:students,student_external_id,' . $student->id,
            'gender' => 'required|in:male,female',
            'stage_id' => 'required|exists:academic_stages,id',
            'group_id' => 'required|exists:academic_groups,id',
            'study_type' => 'required|in:morning,evening',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $path = $request->file('photo')->store('students', 'public');
            $validated['photo_path'] = $path;
        }

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'تم تعديل بيانات الطالب بنجاح.');
    }

    public function show(Student $student, Request $request)
    {
        $student->load([
            'group.stage',
            'warnings'
        ]);

        $attendances = $student->attendances()
            ->with('lecture.subject')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
            'attendances' => $attendances,
        ]);
    }
}


