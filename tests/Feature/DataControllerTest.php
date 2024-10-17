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
    public function it_fetches_data_from_a_table()
    {
        // Setup test data
        DB::table('users')->insert([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->get('/api/data/users');
        $response->assertStatus(200);
        $response->assertJson([
            ['firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@example.com'],
        ]);
    }

    #[Test]
    public function it_returns_404_for_invalid_table()
    {
        $response = $this->get('/api/data/non_existing_table');
        $response->assertStatus(404);
    }
}
