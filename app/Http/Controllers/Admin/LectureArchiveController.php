<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LectureArchiveController extends Controller
{
    /**
     * Display a listing of soft-deleted lectures.
     */
    public function index()
    {
        $lectures = Lecture::onlyTrashed()
            ->with(['subject', 'group.stage', 'teacher'])
            ->latest('deleted_at')
            ->paginate(15);

        return Inertia::render('Admin/Archives/Lectures', [
            'lectures' => $lectures,
        ]);
    }

    /**
     * Restore a soft-deleted lecture.
     */
    public function restore($id)
    {
        $lecture = Lecture::onlyTrashed()->findOrFail($id);
        $lecture->restore();

        return redirect()->back()->with('success', 'تم استعادة المحاضرة بنجاح.');
    }

    /**
     * Permanently delete a lecture.
     */
    public function destroy($id)
    {
        $lecture = Lecture::onlyTrashed()->findOrFail($id);
        
        // Use forceDelete to trigger cascading force deletion if handled in Model boot
        $lecture->forceDelete();

        return redirect()->back()->with('success', 'تم حذف المحاضرة نهائياً.');
    }
}
