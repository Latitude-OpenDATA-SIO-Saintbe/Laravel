<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_the_registration_form()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('app');
    }

    #[Test]
    public function it_registers_a_new_user()
    {
        $response = $this->post('/register', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect('/dashboard');
    }

    #[Test]
    public function it_fails_registration_with_invalid_data()
    {
        $response = $this->post('/register', [
            'firstname' => '',
            'lastname' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'pass',
        ]);

        $response->assertSessionHasErrors(['firstname', 'email', 'password']);
    }
}
