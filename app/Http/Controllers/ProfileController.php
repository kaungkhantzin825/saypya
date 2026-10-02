<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'phone' => $user->phone,
                'country' => $user->country,
                'is_admin' => $user->isAdmin(),
                'is_lecturer' => $user->isLecturer(),
                'is_super_admin' => $user->isSuperAdmin(),
            ],
            'status' => session('status'),
            'title' => 'Profile',
            'description' => 'Manage your account details and password.',
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        // Optional password change — only when the password field was submitted.
        // (`ProfileUpdateRequest` does not cover these fields.)
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // The User model casts `password` to `hashed`, so no manual hashing.
            $user->password = $request->input('password');
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // courses.instructor_id is onDelete('restrict'), so a lecturer who still owns
        // courses cannot be removed — bail out before logging them out.
        if ($user->courses()->count() > 0) {
            return Redirect::route('profile.edit')->withErrors(
                ['password' => 'Your account still owns published courses. Contact an administrator to have them transferred first.'],
                'userDeletion'
            );
        }

        Auth::logout();

        // Hard delete, matching the admin panel: the row leaves the users table rather
        // than lingering with deleted_at set, which would keep the email address taken
        // and block the person from registering again.
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
