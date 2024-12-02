<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    protected $connection = 'pgsql';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->Id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('manager_id')->nullable(); // Reference to manager
            $table->foreign('manager_id')->references('id')->on('users'); // Foreign key to self
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users'); // Drop the users table
    }
}

/**
 * Migration class for creating the 'users' table.
 *
 * This migration creates the 'users' table with the following columns:
 * - id: Primary key, auto-incrementing.
 * - firstname: String, user's first name.
 * - lastname: String, user's last name.
 * - email: String, user's email address, unique.
 * - password: String, user's password.
 * - manager_id: Unsigned big integer, nullable, references 'id' on the same 'users' table.
 * - created_at: Timestamp, automatically managed by Laravel.
 * - updated_at: Timestamp, automatically managed by Laravel.
 *
 * The 'manager_id' column is a foreign key that references the 'id' column on the same 'users' table,
 * establishing a self-referential relationship to denote a user's manager.
 *
 * The 'up' method creates the table, and the 'down' method drops the table.
 *
 * @package Database\Migrations
 */
