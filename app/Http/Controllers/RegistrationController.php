<?php

namespace App\Http\Controllers;

use App\Models\RegistrationForm;
use App\Models\RegistrationSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    /**
     * Show the public registration form.
     */
    public function show(string $slug)
    {
        $form = RegistrationForm::with(['stage', 'group'])
            ->where('slug', $slug)
            ->firstOrFail();

        return Inertia::render('Registration/Show', [
            'form'   => $form,
            'isOpen' => $form->is_open,
        ]);
    }

    /**
     * Handle student submission.
     * QR payload is generated immediately upon submission.
     */
    public function submit(string $slug, Request $request)
    {
        $form = RegistrationForm::where('slug', $slug)->firstOrFail();

        if (!$form->is_open) {
            return back()->withErrors(['form' => 'الاستمارة مغلقة حالياً.']);
        }

        $validated = $request->validate([
            'first_name'          => 'required|string|max:100',
            'second_name'         => 'nullable|string|max:100',
            'last_name'           => 'required|string|max:100',
            'gender'              => 'required|in:male,female',
            'student_external_id' => 'required|string|max:100',
            'photo'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Check for duplicate submission
        if (RegistrationSubmission::where('form_id', $form->id)
            ->where('student_external_id', $validated['student_external_id'])
            ->exists()) {
            return back()->withErrors([
                'student_external_id' => 'هذا الرقم مُسجَّل مسبقاً في هذه الاستمارة.',
            ]);
        }

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('registration_photos', 'public');
        }

        // Generate unique QR payload immediately
        $qrPayload = Str::random(24);

        $submission = RegistrationSubmission::create([
            'form_id'             => $form->id,
            'first_name'          => $validated['first_name'],
            'second_name'         => $validated['second_name'] ?? null,
            'last_name'           => $validated['last_name'],
            'gender'              => $validated['gender'],
            'student_external_id' => $validated['student_external_id'],
            'photo_path'          => $photoPath,
            'qr_payload'          => $qrPayload,
            'status'              => 'pending',
        ]);

        return Inertia::render('Registration/Show', [
            'form'       => $form->load(['stage', 'group']),
            'isOpen'     => $form->is_open,
            'success'    => true,
            'submission' => [
                'id'                  => $submission->id,
                'full_name'           => trim("{$submission->first_name} {$submission->second_name} {$submission->last_name}"),
                'student_external_id' => $submission->student_external_id,
                'qr_payload'          => $submission->qr_payload,
            ],
        ]);
    }

    /**
     * Public lookup: find a student by their external ID and show their QR.
     */
    public function lookup(Request $request)
    {
        $result = null;

        if ($request->filled('id')) {
            $externalId = $request->input('id');

            // First check the students table (approved)
            $student = Student::where('student_external_id', $externalId)->first();

            if ($student) {
                $result = [
                    'status'     => 'approved',
                    'full_name'  => $student->full_name,
                    'id'         => $student->student_external_id,
                    'qr_payload' => $student->qr_payload,
                ];
            } else {
                // Check in pending/rejected submissions
                $submission = RegistrationSubmission::where('student_external_id', $externalId)->latest()->first();

                if ($submission) {
                    $result = [
                        'status'    => $submission->status,
                        'full_name' => $submission->full_name,
                        'id'        => $submission->student_external_id,
                        'qr_payload' => $submission->status === 'approved' ? $submission->qr_payload : null,
                    ];
                }
            }
        }

        return Inertia::render('Registration/Lookup', [
            'result'     => $result,
            'searchedId' => $request->input('id'),
        ]);
    }
}
