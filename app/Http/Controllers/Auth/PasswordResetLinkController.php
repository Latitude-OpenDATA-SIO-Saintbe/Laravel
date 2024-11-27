<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

class PasswordResetLinkController extends Controller
{
    /**
     * Send the password reset link to the user.
     */
    public function store(Request $request)
    {
        // Validate the email input: make sure the email is required, valid, and exists in the 'users' table
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Get the email from the request
        $email = $request->input('email');

        // Check if there is already a recent password reset request for this email (e.g., within the last 10 minutes)
        $recentResetRequest = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('created_at', '>', now()->subMinutes(10)) // You can adjust this time frame
            ->first();

        if ($recentResetRequest) {
            return back()->withErrors(['email' => 'A password reset request has already been made recently for this email.']);
        }

        // Attempt to send the reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Check if the reset link was sent successfully
        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(['status' => 'We have emailed your password reset link!']);
        }

        // If something went wrong, return an error message
        return back()->withErrors(['email' => 'We were unable to find a user with that email address.']);
    }
}

/**
 * PasswordResetLinkController handles the sending of password reset links to users.
 *
 * This controller contains a method to validate the user's email, check for recent password reset requests,
 * and send a password reset link if the conditions are met.
 *
 * Methods:
 * - store(Request $request): Validates the email input, checks for recent password reset requests,
 *   and attempts to send the password reset link to the user.
 *
 * @package App\Http\Controllers\Auth
 */
