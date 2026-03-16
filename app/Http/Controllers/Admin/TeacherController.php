<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('teacher')->withTrashed()->newQuery();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        $teachers = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Teachers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_external_id' => 'nullable|string|max:255|unique:users',
            'department' => 'nullable|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'academic_title' => 'nullable|string|max:255',
            'degree' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile_photos', 'public');
        }

        $user = User::create([
            'teacher_external_id' => $validated['teacher_external_id'] ?? null,
            'department' => $validated['department'] ?? null,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'academic_title' => $validated['academic_title'] ?? null,
            'degree' => $validated['degree'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'photo_path' => $photoPath,
        ]);
        $user->assignRole('teacher');

        return redirect()->route('admin.teachers.index')->with('success', 'تم إضافة الأستاذ بنجاح.');
    }

    public function show(User $teacher)
    {
        return Inertia::render('Admin/Teachers/Show', [
            'teacher' => $teacher->load(['subjects.groups']),
        ]);
    }

    public function edit(User $teacher)
    {
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher,
        ]);
    }

    public function update(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'teacher_external_id' => 'nullable|string|max:255|unique:users,teacher_external_id,'.$teacher->id,
            'department' => 'nullable|string|max:255',
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$teacher->id,
            'password' => 'nullable|string|min:8|confirmed',
            'academic_title' => 'nullable|string|max:255',
            'degree' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $photoPath = $teacher->photo_path;
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('profile_photos', 'public');
        }

        $teacher->update([
            'teacher_external_id' => $validated['teacher_external_id'] ?? null,
            'department' => $validated['department'] ?? null,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => ($validated['password'] ?? null) ? Hash::make($validated['password']) : $teacher->password,
            'academic_title' => $validated['academic_title'] ?? null,
            'degree' => $validated['degree'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('admin.teachers.index')->with('success', 'تم تحديث بيانات الأستاذ بنجاح.');
    }

    public function destroy(User $teacher)
    {
        // For store compliance, we'll deactivate by default instead of deleting data
        $teacher->update(['is_active' => false]);
        $teacher->tokens()->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'تم تعطيل حساب الأستاذ بنجاح.');
    }

    /**
     * Permanent delete if needed (Internal)
     */
    public function permanentDestroy(User $teacher)
    {
        $teacher->delete();
        return redirect()->route('admin.teachers.index')->with('success', 'تم حذف الأستاذ نهائياً بنجاح.');
    }

    public function activate(User $teacher)
    {
        $teacher->update(['is_active' => true]);

        return redirect()->route('admin.teachers.index')->with('success', 'تم إعادة تفعيل حساب الأستاذ بنجاح.');
    }

    public function restore($id)
    {
        $teacher = User::withTrashed()->findOrFail($id);
        $teacher->restore();
        $teacher->update(['is_active' => true]);

        return redirect()->route('admin.teachers.index')->with('success', 'تم استعادة حساب الأستاذ بنجاح.');
    }

    public function revokeSessions(User $teacher)
    {
        $teacher->tokens()->delete();

        return back()->with('success', 'تم إلغاء كافة جلسات الأستاذ (فك الارتباط) بنجاح.');
    }
}
