<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function show(Request $request)
    {
        $user = auth()->user()->load('manager'); // Load manager relationship
        return Inertia::render('Profile', [
            'user' => $user,
            'role' => $user->role,
            'manager' => $user->manager ? [
                'name' => $user->manager->firstname . ' ' . $user->manager->lastname,
                'role' => $user->manager->role
            ] : null
        ]);
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);

        // Update the user data
        $request->user()->update($validatedData);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'password'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('status', 'Password updated successfully!');
    }
}
