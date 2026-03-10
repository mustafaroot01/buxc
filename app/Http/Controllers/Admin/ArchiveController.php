<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    public function index()
    {
        $archivedStudents = Student::onlyTrashed()->with('group.stage')->paginate(15);
        
        return Inertia::render('Admin/Archive/Index', [
            'students' => $archivedStudents
        ]);
    }

    public function restore($id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();

        return redirect()->back()->with('success', 'Student restored successfully.');
    }
}

