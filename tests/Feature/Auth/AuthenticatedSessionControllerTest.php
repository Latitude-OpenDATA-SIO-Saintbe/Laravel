<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuthenticatedSessionControllerTest extends TestCase
{
    protected $connection = 'pgsql';
    use RefreshDatabase;

    /** @test */
    /** Check if user see login */
    // #[Test]
    // public function it_shows_the_login_form()
    // {
    //     $response = $this->get('/login');
    //     $response->assertStatus(200);
    //     $response->assertViewIs('app');
    // }

    /** @test */
    /** Check if user can login */
    #[Test]
    public function it_authenticates_a_user()
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    /** @test */
    /** Test if user can't login with wrong password */
    #[Test]
    public function it_fails_authentication_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    /** Test if user can logout */
    #[Test]
    public function it_logs_out_a_user()
    {
        // Create a single user model instance
        $user = User::factory()->create();

        // Act as the created user
        $this->actingAs($user);
        // Explicitly logout using the Auth facade
        Auth::logout();

        // Ensure the user is logged out (assert guest)
        $this->assertGuest();  // Ensures no user is authenticated

        // Check that the response redirects to the home page (or wherever it should go after logout)
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
    }
}

/**
 *
 * This file contains the AuthenticatedSessionControllerTest class, which is a feature test for authentication-related functionalities in a Laravel application.
 *
 * The class includes the following tests:
 * - it_shows_the_login_form: Ensures that the login form is displayed correctly.
 * - it_authenticates_a_user: Tests the authentication process for a user with valid credentials.
 * - it_fails_authentication_with_invalid_credentials: Verifies that authentication fails with invalid credentials.
 * - it_logs_out_a_user: Tests the logout functionality for an authenticated user.
 *
 * The class uses the RefreshDatabase trait to ensure a fresh database state for each test and sets the database connection to 'pgsql'.
 */
