<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetLinkControllerTest extends TestCase
{

    /** @test */
    public function it_sends_password_reset_link_to_valid_email()
    {
        // Create a user
        $user = User::factory()->create(['email' => 'john-emaill@example.com']);

        // Mock the Password facade
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'john-emaill@example.com'])
            ->andReturn(Password::RESET_LINK_SENT);

        // Send password reset request
        $response = $this->post(route('password.email'), ['email' => 'john-emaill@example.com']);

        // Assert the response
        $response->assertSessionHas('status', 'We have emailed your password reset link!');
    }

    /** @test */
    public function it_fails_to_send_password_reset_link_to_invalid_email()
    {
        // Send password reset request with invalid email
        $response = $this->post(route('password.email'), ['email' => 'invalid@example.com']);

        // Assert the response
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_fails_to_send_password_reset_link_if_recent_request_exists()
    {

        // Insert a recent password reset request
        DB::table('password_reset_tokens')->insert([
            'email' => 'john-emaill@example.com',
            'token' => 'dummy-token',
            'created_at' => now(),
        ]);

        // Send password reset request
        $response = $this->post(route('password.email'), ['email' => 'john-emaill@example.com']);

        // Assert the response
        $response->assertSessionHasErrors(['email' => 'A password reset request has already been made recently for this email.']);
    }

    /** @test */
    public function it_validates_email_field()
    {
        // Send password reset request with invalid email format
        $response = $this->post(route('password.email'), ['email' => 'invalid-email']);

        // Assert the response
        $response->assertSessionHasErrors(['email']);
    }
}
