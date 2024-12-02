<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'pgsql';
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

/**
 * Migration to create 'password_reset_tokens' and 'sessions' tables.
 *
 * This migration creates two tables:
 * 1. 'password_reset_tokens' - Stores email, token, and created_at timestamp for password reset functionality.
 * 2. 'sessions' - Stores session data including user_id, IP address, user agent, payload, and last activity timestamp.
 *
 * The migration uses PostgreSQL as the database connection.
 *
 * Methods:
 * - up(): Creates the 'password_reset_tokens' and 'sessions' tables.
 * - down(): Drops the 'password_reset_tokens' and 'sessions' tables.
 */
