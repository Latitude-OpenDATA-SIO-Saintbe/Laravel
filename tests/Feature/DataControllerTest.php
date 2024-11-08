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
        $response = $this->get('/api/data');
        $response->assertStatus(200);
        $response->assertJson(['users']);
    }

    #[Test]
    public function it_returns_404_for_invalid_table()
    {
        $response = $this->get('/api/data/non_existing_table');
        $response->assertStatus(404);
    }

    #[Test]
    public function it_creates_a_new_row_in_table()
    {
        $data = ['name' => 'Jane Doe', 'email' => 'jane@example.com'];
        $response = $this->post('/api/data/users', $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', $data);
    }

    #[Test]
    public function it_updates_a_row_in_table()
    {
        $user = DB::table('users')->insertGetId(['name' => 'John Doe', 'email' => 'john@example.com']);
        $data = ['name' => 'John Smith', 'email' => 'johnsmith@example.com'];
        $response = $this->put("/api/data/users/{$user}", $data);
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', $data);
    }

    #[Test]
    public function it_fetches_data_from_valid_table()
    {
        DB::table('users')->insert(['name' => 'John Doe', 'email' => 'john@example.com']);
        $response = $this->get('/api/data/users');
        $response->assertStatus(200);
        $response->assertJson([['name' => 'John Doe', 'email' => 'john@example.com']]);
    }

    #[Test]
    public function it_deletes_a_row_in_table()
    {
        $user = DB::table('users')->insertGetId(['name' => 'John Doe', 'email' => 'john@example.com']);
        $response = $this->delete("/api/data/users/{$user}");
        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user]);
    }
}
