<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_lists_tables_excluding_unauthorized_tables()
    {
        $this->withoutMiddleware();
        $response = $this->get('/api/data');
        $response->assertStatus(200);
        $response->assertJson(['users']);
    }

    #[Test]
    public function it_returns_404_for_invalid_table()
    {
        $this->withoutMiddleware();
        $response = $this->get('/api/data/non_existing_table');
        $response->assertStatus(404);
    }

    #[Test]
    public function it_creates_a_new_row_in_table_with_required_fields()
    {
        $this->withoutMiddleware();
        $data = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ];
        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane@example.com'
        ]);
    }

    #[Test]
    public function it_fails_to_create_row_with_duplicate_unique_fields()
    {
        $this->withoutMiddleware();

        // Insert an initial user
        DB::table('users')->insert([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'duplicate@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        // Attempt to insert another user with the same email
        $data = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'duplicate@example.com', // Duplicate email
            'password' => bcrypt('newpassword'),
            'role' => 'user'
        ];

        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(422); // Expecting 422 Unprocessable Entity due to unique constraint
    }

    #[Test]
    public function it_fails_to_create_row_with_invalid_data_types()
    {
        $this->withoutMiddleware();
        DB::table('users')->truncate();

        // Providing an integer for 'firstname' which should be a string
        $data = [
            'firstname' => 12345, // Invalid data type
            'lastname' => 'Doe',
            'email' => 'invaliddatatype@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ];

        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(422); // Expecting 422 Unprocessable Entity due to validation error
    }

    #[Test]
    public function it_fails_to_create_row_with_missing_required_fields()
    {
        $this->withoutMiddleware();

        // Missing required fields like 'firstname' and 'lastname'
        $data = [
            'email' => 'missingfields@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ];

        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(422); // Expecting 422 Unprocessable Entity due to validation failure
    }

    #[Test]
    public function it_updates_a_row_in_table_with_Id_as_primary_key()
    {
        $this->withoutMiddleware();
        $user = DB::table('users')->insertGetId([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);
        $data = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'johnsmith@example.com',
            'password' => bcrypt('newpassword'),
            'role' => 'user'
        ];
        $response = $this->put("/api/data/users/{$user}", $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'johnsmith@example.com'
        ]);
    }

    #[Test]
    public function it_deletes_a_row_in_table_with_Id_as_primary_key()
    {
        $this->withoutMiddleware();
        $user = DB::table('users')->insertGetId([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);
        $response = $this->delete("/api/data/users/{$user}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user]);
    }

    #[Test]
    public function it_handles_deletion_from_empty_table()
    {
        $this->withoutMiddleware();

        // Ensure 'users' table is empty
        DB::table('users')->truncate();

        $response = $this->delete("/api/data/users/1");
        $response->assertStatus(404); // Expecting 404 for non-existent row
    }

    #[Test]
    public function it_returns_404_for_non_existent_row_in_users_table()
    {
        $this->withoutMiddleware();

        // Trying to delete a non-existent user
        $response = $this->delete("/api/data/users/99999");
        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_for_non_existent_row_in_table_with_Id()
    {
        $this->withoutMiddleware();

        // Assuming 'another_table' has 'Id' as the primary key
        $response = $this->delete("/api/data/another_table/99999");
        $response->assertStatus(404);
    }
}
