<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'manager_id', // Assuming you have a manager_id field in your users table
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Define the manager relationship
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

        /**
     * Check if two-factor authentication is enabled for the user.
     *
     * @return bool
     */
    public function hasTwoFactorAuthenticationEnabled()
    {
        return ! is_null($this->two_factor_secret);
    }
}

/**
 * User Model
 *
 * This model represents a user in the application and extends the Authenticatable class provided by Laravel.
 * It includes traits for factory creation, notifications, two-factor authentication, and role management.
 *
 * Traits:
 * - HasFactory: Provides factory methods for creating model instances.
 * - Notifiable: Allows the user to receive notifications.
 * - TwoFactorAuthenticatable: Adds two-factor authentication support.
 * - HasRoles: Adds role-based permissions support.
 *
 * Properties:
 * - $fillable: An array of attributes that are mass assignable.
 * - $hidden: An array of attributes that should be hidden for arrays.
 * - $casts: An array of attributes that should be cast to native types.
 *
 * Relationships:
 * - manager(): Defines a belongsTo relationship with the User model, representing the user's manager.
 *
 * Methods:
 * - hasTwoFactorAuthenticationEnabled(): Checks if two-factor authentication is enabled for the user.
 */
