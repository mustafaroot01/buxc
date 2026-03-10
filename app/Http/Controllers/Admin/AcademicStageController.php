<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicStage;
use Inertia\Inertia;

class AcademicStageController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicStage::query();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $stages = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Stages/Index', [
            'stages' => $stages,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Stages/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_stages,name',
            'description' => 'nullable|string'
        ]);

        AcademicStage::create($validated);

        return redirect()->route('admin.stages.index')->with('success', 'تم إضافة المرحلة الدراسية بنجاح.');
    }

    public function edit(AcademicStage $stage)
    {
        return Inertia::render('Admin/Stages/Edit', [
            'stage' => $stage
        ]);
    }

    public function update(Request $request, AcademicStage $stage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_stages,name,' . $stage->id,
            'description' => 'nullable|string'
        ]);

        $stage->update($validated);

        return redirect()->route('admin.stages.index')->with('success', 'تم تحديث المرحلة الدراسية بنجاح.');
    }

    public function destroy(AcademicStage $stage)
    {
        $stage->delete();
        return redirect()->route('admin.stages.index')->with('success', 'تم حذف المرحلة الدراسية بنجاح.');
    }
}
