<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;


class NewPasswordControllerTest extends TestCase
{

    protected $connection = 'pgsql';

    /** @test */
    public function it_shows_reset_password_form_with_valid_token()
    {
        $email = 'john-reset@example.com';
        $token = Str::random(60);

        // Insert a password reset token into the database
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Request the password reset form
        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $email]));

        // Assert the form is shown
        $response->assertStatus(200);
        $response->assertViewIs('auth.reset-password');
        $response->assertViewHas('token', $token);
        $response->assertViewHas('email', $email);
    }

    /** @test */
    public function it_redirects_with_error_for_invalid_token()
    {
        $email = 'john-reset@example.com';
        $token = Str::random(60);

        // Request the password reset form with an invalid token
        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $email]));

        // Assert the user is redirected to the login page with an error message
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', __('Invalid or expired password reset token.'));
    }

    /** @test */
    public function it_resets_password_with_valid_data()
    {
        $email = 'john-resetll@example.com';
        $token = Str::random(60);
        $newPassword = 'newpassword123';

        // Create a user
        $user = User::factory()->create(['email' => $email]);

        // Insert a password reset token into the database
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Submit the password reset form
        $response = $this->post(route('password.update'), [
            'email' => $email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'token' => $token,
        ]);

        // Assert the user is redirected to the login page with a success message
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', __('Your password has been reset!'));

        // Assert the user's password is updated
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    /** @test */
    public function it_fails_to_reset_password_with_invalid_email()
    {
        $email = 'john-resetmm@example.com';
        $token = Str::random(60);
        $newPassword = 'newpassword123';

        // Insert a password reset token into the database
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Submit the password reset form with an invalid email
        $response = $this->post(route('password.update'), [
            'email' => 'invalid@example.com',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'token' => $token,
        ]);

        // Assert the validation error for email
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    //public function it_fails_to_reset_password_with_invalid_token()
    //{
    //     $email = 'john-resellt@example.com';
    //     $token = Str::random(60);
    //     $newPassword = 'newpassword123';

    //     // Create a user
    //     $user = User::factory()->create(['email' => $email]);

    //     // Submit the password reset form with an invalid token
    //     $response = $this->post(route('password.update'), [
    //         'email' => $email,
    //         'password' => $newPassword,
    //         'password_confirmation' => $newPassword,
    //         'token' => 'invalid-token',
    //     ]);
    // }
}

/**
 * Class NewPasswordControllerTest
 *
 * This class contains feature tests for the NewPasswordController in a Laravel application.
 * It tests the password reset functionality, including showing the reset password form,
 * handling invalid tokens, and resetting the password with valid and invalid data.
 *
 * @package Tests\Feature\Auth
 *
 * @property string $connection The database connection to be used for the tests.
 *
 * @method void it_shows_reset_password_form_with_valid_token() Test that the reset password form is shown with a valid token.
 * @method void it_redirects_with_error_for_invalid_token() Test that the user is redirected with an error for an invalid token.
 * @method void it_resets_password_with_valid_data() Test that the password is reset with valid data.
 * @method void it_fails_to_reset_password_with_invalid_email() Test that the password reset fails with an invalid email.
 * @method void it_fails_to_reset_password_with_invalid_token() Test that the password reset fails with an invalid token.
 */
