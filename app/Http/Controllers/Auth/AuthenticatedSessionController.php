<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /** use connection for database */
    protected $connection = 'pgsql';

    /**
     * Show the login form.
     */
    public function create(Request $request)
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        // Validate the login credentials
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to log in the user
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = Auth::user();

            // // Check if 2FA is enabled and handle 2FA code verification
            // if ($user->hasTwoFactorAuthenticationEnabled()) {
            //     // Fortify will automatically handle the 2FA prompt after this step
            //     return redirect()->route('two-factor.index');  // Fortify redirects to this route for 2FA verification
            // }

            // If 2FA is not enabled, continue the session
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        // If login failed, return with error
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

/**
 * Class AuthenticatedSessionController
 *
 * This controller handles the authentication sessions for the application.
 * It includes methods for showing the login form, handling login requests,
 * and logging out users.
 *
 * @package App\Http\Controllers\Auth
 */

 /**
    * Show the login form.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Inertia\Response|\Illuminate\Http\RedirectResponse
    */

 /**
    * Handle an incoming authentication request.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\RedirectResponse
    * @throws \Illuminate\Validation\ValidationException
    */

 /**
    * Log the user out of the application.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\RedirectResponse
    */
