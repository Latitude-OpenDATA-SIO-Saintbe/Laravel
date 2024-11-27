<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Invite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /** connection do db */
    protected $connection = 'pgsql';

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request)
    {
        // Validate registration data
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'token' => 'required|string|exists:invites,token',  // Validate invite token
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check if the invite token is valid and not expired
        $invite = Invite::where('token', $request->token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invite) {
            return redirect()->back()->withErrors(['invite' => 'The invite token is invalid or expired.'])->withInput();
        }

        // Create a new user
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign the 'moderateur' role to the user
        $user->assignRole('moderateur');  // Assigning the role

        // Enable Two-Factor Authentication for the user (optional)
        // if (Features::enabled(Features::twoFactorAuthentication())) {
        //     $user->createTwoFactorAuth();  // This method generates the 2FA secret
        // }

        // Delete the invite after it's used (to prevent reuse)
        $invite->delete();  // You can adjust this as per your invite logic

        // Log the user in
        Auth::login($user);

        // Redirect to the dashboard
        return redirect()->route('dashboard');
    }
}

/**
 * RegisteredUserController handles the registration of new users.
 *
 * This controller is responsible for validating registration data,
 * checking invite tokens, creating new users, assigning roles,
 * enabling two-factor authentication, and logging in the user.
 *
 * @package App\Http\Controllers\Auth
 */
