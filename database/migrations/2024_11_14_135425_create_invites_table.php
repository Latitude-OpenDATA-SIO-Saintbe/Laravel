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
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique(); // Unique invite token
            $table->timestamp('expires_at')->nullable(); // Expiration date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};

/**
 * Migration to create the invites table.
 *
 * This migration creates the invites table with the following columns:
 * - id: Primary key.
 * - token: Unique invite token.
 * - expires_at: Expiration date, nullable.
 * - created_at: Timestamp when the invite was created.
 * - updated_at: Timestamp when the invite was last updated.
 *
 * The migration uses the PostgreSQL connection.
 *
 * Methods:
 * - up(): Creates the invites table.
 * - down(): Drops the invites table if it exists.
 */
