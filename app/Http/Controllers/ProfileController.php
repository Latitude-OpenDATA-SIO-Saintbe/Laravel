<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{
    protected $connection = 'pgsql';
    /**
     * Show the user's profile.
     */
    public function show(Request $request)
    {
        // Load the manager relationship and the user's roles (with Spatie/Permission)
        $user = auth()->user()->load('manager:id,firstname,lastname', 'roles');

        // Proceed with role extraction
        $role = $user->roles->first();

        // Debugging: Check the roles relationship
        //dd($role);  // Dump the roles to inspect the data

        $roleName = $role->name;

        return Inertia::render('Profile', [
            'user' => $user,
            'role' => $roleName,
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
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    return $fail(__('The provided password does not match your current password.'));
                }
            }],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('status', 'Password updated successfully!');
    }
}

/**
 * Class ProfileController
 *
 * This controller handles the display and update of user profiles.
 *
 * @package App\Http\Controllers
 */

 /**
    * Show the user's profile.
    *
    * This method retrieves the authenticated user's profile, including their manager's information
    * and roles, and renders the 'Profile' view using Inertia.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Inertia\Response
    */

 /**
    * Update the user's profile.
    *
    * This method validates and updates the authenticated user's profile information.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\RedirectResponse
    */

 /**
    * Update the user's password.
    *
    * This method validates the current password and updates it to a new password for the authenticated user.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\RedirectResponse
    */
