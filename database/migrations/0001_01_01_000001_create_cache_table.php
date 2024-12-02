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
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};

/**
 * Migration to create cache and cache_locks tables.
 *
 * This migration creates two tables: 'cache' and 'cache_locks'.
 *
 * The 'cache' table has the following columns:
 * - key: Primary key, string type.
 * - value: Medium text type to store the cache value.
 * - expiration: Integer type to store the expiration time.
 *
 * The 'cache_locks' table has the following columns:
 * - key: Primary key, string type.
 * - owner: String type to store the owner of the lock.
 * - expiration: Integer type to store the expiration time.
 *
 * The migration uses the PostgreSQL connection.
 *
 * Methods:
 * - up(): Creates the 'cache' and 'cache_locks' tables.
 * - down(): Drops the 'cache' and 'cache_locks' tables.
 */
