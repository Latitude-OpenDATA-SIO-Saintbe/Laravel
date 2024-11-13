<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test updating the user's profile.
     */
    public function test_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/profile', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_update_profile_with_invalid_data(): void
    {
        $user = User::factory()->create();
    
        $response = $this->actingAs($user)->put('/profile', [
            'firstname' => '',
            'lastname' => 'Doe',
            'email' => 'not-an-email',
        ]);
    
        $response->assertStatus(302); // Expect a redirect (302) on validation failure
        $response->assertSessionHasErrors(['firstname', 'email']); // Check for errors
    }
    


    /**
     * Test updating the user's password.
     */
    public function test_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user)->put('/profile/password/update', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Password updated successfully!');

        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_update_password_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
    
        $response = $this->actingAs($user)->put('/profile/password/update', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
        ]);
    
        $response->assertSessionHasErrors(['current_password' => 'The provided password does not match your current password.']); // Check if error is returned
    }
}
