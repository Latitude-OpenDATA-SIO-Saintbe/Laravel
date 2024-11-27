<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Database\Seeders\RolesAndPermissionsSeeder;
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        DB::table('invites')->insert([
            'token' => 'PNIdS90A9vO5x4cUoYMSMLFWic7WWvjE',
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->call(RolesAndPermissionsSeeder::class);
    }
}

/**
 * Class DatabaseSeeder
 *
 * This class is responsible for seeding the database with initial data.
 * It inserts a record into the 'invites' table with a token and expiration date,
 * and calls the RolesAndPermissionsSeeder to seed roles and permissions.
 *
 * @package Database\Seeders
 */
