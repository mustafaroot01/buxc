<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicStage;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicStage::with(['subjects.teacher', 'subjects.groups'])->orderBy('level');

        if ($request->filled('search')) {
            $query->whereHas('subjects', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            })->with(['subjects' => function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')->with('teacher', 'groups');
            }]);
        }

        if ($request->filled('stage_id')) {
            $query->where('id', $request->stage_id);
        }

        $stagesWithSubjects = $query->paginate(15)->withQueryString();
        $stages = AcademicStage::all(['id', 'name']);

        return Inertia::render('Admin/Subjects/Index', [
            'stagesWithSubjects' => $stagesWithSubjects,
            'stages' => $stages,
            'filters' => $request->only('search', 'stage_id'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Subjects/Create', [
            'stages' => AcademicStage::with('groups')->get(['id', 'name']),
            'teachers' => User::role('teacher')->get(['id', 'full_name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'stage_id' => 'required|exists:academic_stages,id',
            'teacher_id' => 'required|exists:users,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:academic_groups,id',
        ]);

        $subject = Subject::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'stage_id' => $validated['stage_id'],
            'teacher_id' => $validated['teacher_id'],
        ]);

        if (isset($validated['group_ids'])) {
            $subject->groups()->attach($validated['group_ids']);
        }

        return redirect()->route('admin.subjects.index')->with('success', 'تم إضافة المادة الدراسية بنجاح.');
    }

    public function edit(Subject $subject)
    {
        return Inertia::render('Admin/Subjects/Edit', [
            'subject' => $subject->load(['stage', 'teacher', 'groups']),
            'stages' => AcademicStage::with('groups')->get(['id', 'name']),
            'teachers' => User::role('teacher')->get(['id', 'full_name']),
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,'.$subject->id,
            'stage_id' => 'required|exists:academic_stages,id',
            'teacher_id' => 'required|exists:users,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'exists:academic_groups,id',
        ]);

        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'stage_id' => $validated['stage_id'],
            'teacher_id' => $validated['teacher_id'],
        ]);

        if (isset($validated['group_ids'])) {
            $subject->groups()->sync($validated['group_ids']);
        } else {
            $subject->groups()->detach();
        }

        return redirect()->route('admin.subjects.index')->with('success', 'تم تحديث المادة الدراسية بنجاح.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'تم حذف المادة الدراسية بنجاح.');
    }
}
