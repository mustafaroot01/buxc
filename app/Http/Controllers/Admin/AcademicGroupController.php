<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicGroup;
use App\Models\AcademicStage;
use Inertia\Inertia;

class AcademicGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $stages = \App\Models\AcademicStage::with(['groups' => function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            }
        }])->paginate(15)->withQueryString();

        return Inertia::render('Admin/Groups/Index', [
            'stages' => $stages,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Groups/Create', [
            'stages' => AcademicStage::all(['id', 'name'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stage_id' => 'required|exists:academic_stages,id',
            'study_type' => 'required|in:morning,evening',
        ]);

        AcademicGroup::create($validated);

        return redirect()->route('admin.groups.index')->with('success', 'تم إضافة المجموعة الدراسية بنجاح.');
    }

    public function edit(AcademicGroup $group)
    {
        return Inertia::render('Admin/Groups/Edit', [
            'group' => $group->load('stage'),
            'stages' => AcademicStage::all(['id', 'name'])
        ]);
    }

    public function update(Request $request, AcademicGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stage_id' => 'required|exists:academic_stages,id',
            'study_type' => 'required|in:morning,evening',
        ]);

        $group->update($validated);

        return redirect()->route('admin.groups.index')->with('success', 'تم تحديث المجموعة الدراسية بنجاح.');
    }

    public function destroy(AcademicGroup $group)
    {
        $group->delete();
        return redirect()->route('admin.groups.index')->with('success', 'تم حذف المجموعة الدراسية بنجاح.');
    }
}
