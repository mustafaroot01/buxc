<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the current teacher's profile.
     */
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'photo_url' => $request->user()->photo_path ? asset('storage/' . $request->user()->photo_path) : null,
        ]);
    }

    /**
     * Update the teacher's profile info.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'academic_title' => 'nullable|string|max:255',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('profile-photos', 'public');
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
            'photo_url' => $user->photo_path ? asset('storage/' . $user->photo_path) : null,
        ]);
    }

    /**
     * Deactivate the teacher's account.
     */
    public function deactivate(Request $request)
    {
        $user = $request->user();

        // Mark as inactive
        $user->update(['is_active' => false]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Account deactivated successfully.',
        ]);
    }
}
