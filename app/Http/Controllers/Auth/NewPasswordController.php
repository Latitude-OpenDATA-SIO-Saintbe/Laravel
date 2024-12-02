<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class NewPasswordController extends Controller
{
    /**
     * Reset the user's password.
     */
    public function create($token, Request $request)
    {
        // Extract the email from the query string
        $email = $request->query('email');

        // Retrieve the reset record from the database using the token and email
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        // If no record found, redirect back with an error
        if (!$passwordReset) {
            return redirect()->route('login')->with('error', __('Invalid or expired password reset token.'));
        }

        // Assuming the token in the database is hashed with bcrypt, we use Hash::check() to verify
        if (!Hash::check($token, $passwordReset->token)) {
            return redirect()->route('login')->with('error', __('Invalid or expired password reset token.'));
        }

        // Show the password reset form
        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
    }

    /**
     * Reset the user's password.
     */
    public function store(Request $request)
    {
        // Validate email and password inputs
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => __('We could not find a user with that email address.'),
            ]);
        }

        // Update password
        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Redirect after successful reset
        return redirect()->route('login')->with('status', __('Your password has been reset!'));
    }
}

/**
 * Class NewPasswordController
 *
 * This controller handles the resetting of user passwords. It includes methods to show the password reset form and to process the password reset request.
 *
 * @package App\Http\Controllers\Auth
 */

 /**
    * Show the form to reset the user's password.
    *
    * @param string $token The password reset token.
    * @return \Illuminate\View\View The view for resetting the password.
    */

 /**
    * Reset the user's password.
    *
    * @param \Illuminate\Http\Request $request The HTTP request containing the password reset data.
    * @return \Illuminate\Http\RedirectResponse A redirect response to the login page with a status message.
    * @throws \Illuminate\Validation\ValidationException If the password reset fails.
    */
