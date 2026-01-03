<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    /**
     * Display the profile page
     */
    public function index()
    {
        return view('profile.index');
    }

    /**
     * Get user profile data (API)
     */
    public function show()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'family' => $user->family,
                'email' => $user->email,
                'phone' => $user->phone,
                'national_id' => $user->national_id,
                'postal_code' => $user->postal_code,
                'birth_date' => $user->birth_date,
                'address' => $user->address,
                'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                'voice_gender' => $user->voice_gender ?? 'female',
                'created_at' => $user->created_at->format('Y-m-d'),
            ]
        ]);
    }

    /**
     * Update user profile (API)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'phone' => 'nullable|string|max:15',
            'national_id' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'voice_gender' => 'nullable|in:male,female',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('profile.updated_successfully'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'family' => $user->family,
                'email' => $user->email,
                'phone' => $user->phone,
                'national_id' => $user->national_id,
                'postal_code' => $user->postal_code,
                'birth_date' => $user->birth_date,
                'address' => $user->address,
                'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
                'voice_gender' => $user->voice_gender ?? 'female',
            ]
        ]);
    }

    /**
     * Update password (API)
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => __('profile.current_password_incorrect'),
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => __('profile.password_updated'),
        ]);
    }

    /**
     * Upload avatar (API)
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::exists($user->avatar)) {
            Storage::delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'message' => __('profile.avatar_updated'),
            'avatar' => Storage::url($path),
        ]);
    }

    /**
     * Delete avatar (API)
     */
    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::exists($user->avatar)) {
            Storage::delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => __('profile.avatar_deleted'),
        ]);
    }
}

