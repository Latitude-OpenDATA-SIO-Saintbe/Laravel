<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';

    // Define the table associated with the model (optional if table name matches the model)
    protected $table = 'invites';

    // Define the fillable attributes to allow mass-assignment
    protected $fillable = [
        'token',
        'expires_at',
    ];

    // Optionally, cast the 'expires_at' to a Carbon instance
    protected $casts = [
        'expires_at' => 'datetime',
    ];
}

/**
 * Invite Model
 *
 * This model represents an invitation in the application.
 * It uses the PostgreSQL database connection and interacts with the 'invites' table.
 *
 * Attributes:
 * - token: The unique token for the invitation.
 * - expires_at: The expiration date and time of the invitation.
 *
 * Fillable attributes:
 * - token
 * - expires_at
 *
 * Casts:
 * - expires_at: Casts the attribute to a Carbon instance (datetime).
 *
 * Traits:
 * - HasFactory: Provides factory methods for the model.
 */
