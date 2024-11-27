<?php

namespace App\Http\Controllers\Auth;

use App\Models\Invite;
use App\Mail\InviteCreated;  // Add the InviteCreated mailable
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Inertia\Inertia; // Add the Inertia facade

class InviteController extends Controller
{
    protected $connection = 'pgsql';

    /**
     * Generate a new invite and send it to the specified email.
     */
    public function create(Request $request)
    {
        // Validate request data (including the email)
        $request->validate([
            'email' => 'required|email',
            'expires_at' => 'nullable|date|after:today', // Optional expiration date
        ]);

        try {
            // Generate a new invite token
            $token = Str::random(32); // Generate a random token (can be adjusted)

            // Set expiration date (default 1 day if not specified)
            $expiresAt = $request->expires_at ? Carbon::parse($request->expires_at) : Carbon::now()->addDays(1);

            // Store invite in the database
            $invite = Invite::create([
                'token' => $token,
                'expires_at' => $expiresAt,
            ]);

            // Send email with the invite token
            Mail::to($request->email)->send(new InviteCreated($invite));

            return response()->json(['message' => 'Invite created and email sent successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create invite: ' . $e->getMessage()], 500);
        }
    }

    // Show invite page (for registration)
    public function show($token)
    {
        // Check if the invite exists and is not expired
        $invite = Invite::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$invite) {
            // Token is invalid or expired
            return redirect('/')->withErrors(['invite' => 'This invite link is invalid or has expired.']);
        }

        // If valid, proceed with the registration page
        return Inertia::render('Auth/Register', ['token' => $token]);
    }
}

/**
 * InviteController handles the creation and management of invitation tokens.
 *
 * This controller is responsible for generating new invite tokens, sending them via email,
 * and displaying the registration page for valid invite tokens.
 *
 * @package App\Http\Controllers\Auth
 */
