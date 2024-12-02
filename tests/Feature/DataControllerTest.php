<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DataControllerTest extends TestCase
{
    protected $connection = 'pgsql';
    use RefreshDatabase;

    /** @test */
    /** Test all table that is list in database */
    #[Test]
    public function it_lists_tables_excluding_unauthorized_tables()
    {
        $this->withoutMiddleware();
        $response = $this->get('/api/data');
        $response->assertStatus(200);
        $response->assertJson(['users']);
    }

    /** @test */
    /** Test invalid table */
    #[Test]
    public function it_returns_404_for_invalid_table()
    {
        $this->withoutMiddleware();
        $response = $this->get('/api/data/non_existing_table');
        $response->assertStatus(404);
    }

    /** @test */
    /** Test create a new row in table with required fields */
    #[Test]
    public function it_creates_a_new_row_in_table_with_required_fields()
    {
        $this->withoutMiddleware();
        $data = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ];
        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'jane@example.com'
        ]);
    }

    /** @test */
    /** Test create a new row in table with duplicate unique fields */
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
        ]);

        // Attempt to insert another user with the same email
        $data = [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'email' => 'duplicate@example.com', // Duplicate email
            'password' => bcrypt('newpassword'),
        ];

        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(422); // Expecting 422 Unprocessable Entity due to unique constraint
    }

    /** @test */
    /** Test create a new row in table with invalid data types */
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
        ];

        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(422); // Expecting 422 Unprocessable Entity due to validation error
    }

    /** @test */
    /** Test create a new row in table with missing required fields */
    #[Test]
    public function it_fails_to_create_row_with_missing_required_fields()
    {
        $this->withoutMiddleware();

        // Missing required fields like 'firstname' and 'lastname'
        $data = [
            'email' => 'missingfields@example.com',
            'password' => bcrypt('password'),
        ];

        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(422); // Expecting 422 Unprocessable Entity due to validation failure
    }

    /** @test */
    /** Test update a row in table with Id as primary key */
    #[Test]
    public function it_updates_a_row_in_table_with_Id_as_primary_key()
    {
        $this->withoutMiddleware();
        $user = DB::table('users')->insertGetId([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $data = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'johnsmith@example.com',
            'password' => bcrypt('newpassword'),
        ];
        $response = $this->put("/api/data/users/{$user}", $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'johnsmith@example.com'
        ]);
    }

    /** @test */
    /** Test delete a row in table with Id as primary key */
    #[Test]
    public function it_deletes_a_row_in_table_with_Id_as_primary_key()
    {
        $this->withoutMiddleware();
        $user = DB::table('users')->insertGetId([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);
        $response = $this->delete("/api/data/users/{$user}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user]);
    }

    /** @test */
    /** Test handle deletion from empty table */
    #[Test]
    public function it_handles_deletion_from_empty_table()
    {
        $this->withoutMiddleware();

        // Ensure 'users' table is empty
        DB::table('users')->truncate();

        $response = $this->delete("/api/data/users/1");
        $response->assertStatus(404); // Expecting 404 for non-existent row
    }

    /** @test */
    /** Test return 404 for non-existent row in users table */
    #[Test]
    public function it_returns_404_for_non_existent_row_in_users_table()
    {
        $this->withoutMiddleware();

        // Trying to delete a non-existent user
        $response = $this->delete("/api/data/users/99999");
        $response->assertStatus(404);
    }

    /** @test */
    /** Test return 404 for non-existent row in table with Id */
    #[Test]
    public function it_returns_404_for_non_existent_row_in_table_with_Id()
    {
        $this->withoutMiddleware();

        // Assuming 'another_table' has 'Id' as the primary key
        $response = $this->delete("/api/data/another_table/99999");
        $response->assertStatus(404);
    }
}

/**
 *
 * This file contains the DataControllerTest class which includes various test cases for the DataController.
 * The tests cover functionalities such as listing tables, handling invalid tables, creating rows with required fields,
 * handling duplicate unique fields, invalid data types, missing required fields, updating rows, deleting rows,
 * and handling non-existent rows.
 *
 * The following test cases are included:
 *
 * - it_lists_tables_excluding_unauthorized_tables: Tests if the API lists tables excluding unauthorized ones.
 * - it_returns_404_for_invalid_table: Tests if the API returns a 404 status for an invalid table.
 * - it_creates_a_new_row_in_table_with_required_fields: Tests if a new row is created in the table with required fields.
 * - it_fails_to_create_row_with_duplicate_unique_fields: Tests if the API fails to create a row with duplicate unique fields.
 * - it_fails_to_create_row_with_invalid_data_types: Tests if the API fails to create a row with invalid data types.
 * - it_fails_to_create_row_with_missing_required_fields: Tests if the API fails to create a row with missing required fields.
 * - it_updates_a_row_in_table_with_Id_as_primary_key: Tests if a row is updated in the table using Id as the primary key.
 * - it_deletes_a_row_in_table_with_Id_as_primary_key: Tests if a row is deleted in the table using Id as the primary key.
 * - it_handles_deletion_from_empty_table: Tests if the API handles deletion from an empty table.
 * - it_returns_404_for_non_existent_row_in_users_table: Tests if the API returns a 404 status for a non-existent row in the users table.
 * - it_returns_404_for_non_existent_row_in_table_with_Id: Tests if the API returns a 404 status for a non-existent row in a table with Id as the primary key.
 */
