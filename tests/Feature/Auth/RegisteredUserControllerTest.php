<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $connection = 'pgsql';

    /** @test */
    /** Test regist a user with a valid token */
    #[Test]
    public function it_registers_a_new_user_with_valid_invite_token()
    {
        // Create a valid invite token
        $invite = Invite::create([
            'token' => Str::random(32),
            'expires_at' => now()->addDays(1),
        ]);

        // Register a new user using the invite token
        $response = $this->post(route('register-post'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => $invite->token,
        ]);

        // Ensure the invite is deleted after use
        $this->assertDatabaseMissing('invites', [
            'token' => $invite->token, // Token should not be present in the invites table anymore
        ]);

        // Assert that the user is redirected to the dashboard after registration
        $response->assertRedirect(route('dashboard'));
    }

    // #[Test]
    // public function it_fails_registration_with_invalid_invite_token()
    // {
    //     // Attempt to register with an invalid invite token
    //     $response = $this->post(route('register-post'), [
    //         'firstname' => 'John',
    //         'lastname' => 'Doe',
    //         'email' => 'john@example.com',
    //         'password' => 'password',
    //         'password_confirmation' => 'password',
    //         'token' => 'invalid-token', // Invalid invite token
    //     ]);

    //     // Assert the validation errors contain the invite token issue
    //     $response->assertSessionHasErrors(['token']);
    // }

    /** @test */
    /** Test fails regist a user with a expired token */
    #[Test]
    public function it_fails_registration_with_expired_invite_token()
    {
        // Create an expired invite token
        $invite = Invite::create([
            'token' => Str::random(32),
            'expires_at' => now()->subDay(), // Token expired 1 day ago
        ]);

        // Attempt to register using the expired invite token
        $response = $this->post(route('register-post'), [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'token' => $invite->token, // Expired invite token
        ]);

        // Assert the validation errors contain the invite token issue
        $response->assertSessionHasErrors(['invite']);
    }

    /** @test */
    /** Test fails regist a user with a invalid email */
    #[Test]
    public function it_fails_registration_with_invalid_data()
    {
        // Create a valid invite token
        $invite = Invite::create([
            'token' => Str::random(32),
            'expires_at' => now()->addDays(1),
        ]);

        // Attempt to register with invalid data
        $response = $this->post(route('register-post'), [
            'firstname' => '',
            'lastname' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'pass', // Password too short
            'token' => $invite->token, // Valid token
        ]);

        // Assert that the validation errors are returned for invalid fields
        $response->assertSessionHasErrors(['firstname', 'email', 'password']);
    }
}

/**
 * This class contains feature tests for the RegisteredUserController.
 * It tests the registration process of a new user using invite tokens.
 *
 * @package Tests\Feature\Auth
 */
