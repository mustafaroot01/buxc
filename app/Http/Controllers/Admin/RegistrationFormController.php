<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicGroup;
use App\Models\AcademicStage;
use App\Models\RegistrationForm;
use App\Models\RegistrationSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegistrationFormController extends Controller
{
    /**
     * List all registration forms.
     */
    public function index()
    {
        $forms = RegistrationForm::with(['stage', 'group'])
            ->withCount(['submissions', 'submissions as pending_count' => function ($q) {
                $q->where('status', 'pending');
            }])
            ->latest()
            ->get();

        $stages = AcademicStage::with('groups')->get();

        return Inertia::render('Admin/Registrations/Index', [
            'forms'  => $forms,
            'stages' => $stages,
        ]);
    }

    /**
     * Create a new registration form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'stage_id'   => 'required|exists:academic_stages,id',
            'group_id'   => 'required|exists:academic_groups,id',
            'study_type' => 'required|in:morning,evening',
        ]);

        $validated['slug'] = Str::random(10);

        RegistrationForm::create($validated);

        return back()->with('success', 'تم إنشاء الاستمارة بنجاح.');
    }

    /**
     * Toggle form open/closed state.
     */
    public function toggle($id)
    {
        $form = RegistrationForm::findOrFail($id);
        $form->update(['is_open' => !$form->is_open]);

        return back()->with('success', $form->is_open ? 'تم فتح الاستمارة.' : 'تم إغلاق الاستمارة.');
    }

    /**
     * Show submissions for a specific form.
     */
    public function submissions($id, Request $request)
    {
        $form = RegistrationForm::with(['stage', 'group'])->findOrFail($id);

        $query = RegistrationSubmission::where('form_id', $id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest()->get();

        return Inertia::render('Admin/Registrations/Submissions', [
            'form'        => $form,
            'submissions' => $submissions,
            'filters'     => $request->only('status'),
        ]);
    }

    /**
     * Approve a single submission → create Student record.
     */
    public function approve($id, $submissionId)
    {
        $form       = RegistrationForm::findOrFail($id);
        $submission = RegistrationSubmission::where('form_id', $id)->findOrFail($submissionId);

        if ($submission->status === 'approved') {
            return back()->with('info', 'هذا الطالب تمت الموافقة عليه مسبقاً.');
        }

        // Create student record
        Student::create([
            'first_name'          => $submission->first_name,
            'second_name'         => $submission->second_name,
            'last_name'           => $submission->last_name,
            'gender'              => $submission->gender,
            'student_external_id' => $submission->student_external_id,
            'photo_path'          => $submission->photo_path,
            'group_id'            => $form->group_id,
            'qr_payload'          => $submission->qr_payload,
        ]);

        $submission->update(['status' => 'approved']);

        return back()->with('success', 'تمت الموافقة وإضافة الطالب للنظام.');
    }

    /**
     * Approve ALL pending submissions in a form.
     */
    public function approveAll($id)
    {
        $form        = RegistrationForm::findOrFail($id);
        $submissions = RegistrationSubmission::where('form_id', $id)
            ->where('status', 'pending')
            ->get();

        foreach ($submissions as $submission) {
            // Skip if student ID already exists in students table
            if (Student::where('student_external_id', $submission->student_external_id)->exists()) {
                $submission->update(['status' => 'approved']);
                continue;
            }

            Student::create([
                'first_name'          => $submission->first_name,
                'second_name'         => $submission->second_name,
                'last_name'           => $submission->last_name,
                'gender'              => $submission->gender,
                'student_external_id' => $submission->student_external_id,
                'photo_path'          => $submission->photo_path,
                'group_id'            => $form->group_id,
                'qr_payload'          => $submission->qr_payload,
            ]);

            $submission->update(['status' => 'approved']);
        }

        return back()->with('success', 'تمت الموافقة الجماعية وإضافة جميع الطلاب.');
    }

    /**
     * Reject a submission.
     */
    public function reject($id, $submissionId)
    {
        $submission = RegistrationSubmission::where('form_id', $id)->findOrFail($submissionId);
        $submission->update(['status' => 'rejected']);

        return back()->with('success', 'تم رفض الطلب.');
    }

    /**
     * Delete a registration form.
     */
    public function destroy($id)
    {
        $form = RegistrationForm::findOrFail($id);

        // Delete uploaded photos for submissions
        $form->submissions->each(function ($sub) {
            if ($sub->photo_path) {
                Storage::disk('public')->delete($sub->photo_path);
            }
        });

        $form->delete();

        return back()->with('success', 'تم حذف الاستمارة.');
    }
}
