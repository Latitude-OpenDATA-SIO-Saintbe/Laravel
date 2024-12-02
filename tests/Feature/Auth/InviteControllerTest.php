<?php
namespace Tests\Feature\Auth;

use App\Models\Invite;
use App\Mail\InviteCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class InviteControllerTest extends TestCase
{

    /** @test */
    #[Test]
    public function it_creates_an_invite_and_sends_email()
    {
        $this->withoutMiddleware();
        Mail::fake();

        $response = $this->post('/invite/create', [
            'email' => 'test@example.com',
            'expires_at' => Carbon::now()->addDays(1)->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Invite created and email sent successfully.']);

        Mail::assertSent(InviteCreated::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    #[Test]
    public function it_fails_to_create_invite_with_invalid_email()
    {
        $this->withoutMiddleware();
        $response = $this->post('/invite/create', [
            'email' => 'invalid-email',
            'expires_at' => Carbon::now()->addDays(1)->toDateString(),
        ]);

        $response->assertStatus(302); // detect missing fields but return 302 error and not 422
        $response->assertSessionHasErrors(['email']);
    }
}

/**
 * This class contains feature tests for the InviteController.
 *
 * It includes tests to verify the creation of invites and the sending of emails,
 * as well as handling invalid email inputs.
 *
 * Methods:
 *
 * - it_creates_an_invite_and_sends_email: Tests that an invite is created and an email is sent successfully.
 * - it_fails_to_create_invite_with_invalid_email: Tests that the invite creation fails when an invalid email is provided.
 *
 * Dependencies:
 * - App\Models\Invite
 * - App\Mail\InviteCreated
 * - Illuminate\Foundation\Testing\RefreshDatabase
 * - Illuminate\Support\Facades\Mail
 * - Illuminate\Support\Str
 * - Carbon\Carbon
 * - Tests\TestCase
 * - PHPUnit\Framework\Attributes\Test
 */
