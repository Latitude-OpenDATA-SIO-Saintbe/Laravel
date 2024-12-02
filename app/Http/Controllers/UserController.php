<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class UserController extends Controller
{
    protected $connection = 'pgsql';
    public function enable2FA(Request $request)
    {
        $user = Auth::user();
        $google2fa = app(Google2FA::class);

        // Generate a new secret key
        $secret = $google2fa->generateSecretKey();

        // Save the secret key in the user's record
        $user->google2fa_secret = $secret;
        if ($user instanceof \Illuminate\Database\Eloquent\Model) {
            $user->save();
        } else {
            // Handle the error appropriately
            return response()->json(['error' => 'User model not found'], 500);
        }

        // Generate the QR code URL that the user can scan
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // Return the QR code URL to the view
        return view::render('enableTwoFactor', ['qrCodeUrl' => $qrCodeUrl, 'secret' => $secret]);
    }
}

/**
 * Class UserController
 *
 * This controller handles the enabling of two-factor authentication (2FA) for users.
 *
 * @package App\Http\Controllers
 */

 /**
    * Enable 2FA for the authenticated user.
    *
    * This method generates a new secret key for Google 2FA, saves it to the user's record,
    * and returns a QR code URL that the user can scan to set up 2FA.
    *
    * @param \Illuminate\Http\Request $request
    * @return \Illuminate\Http\Response
    */
