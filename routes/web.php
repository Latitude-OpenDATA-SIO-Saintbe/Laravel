<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\InviteController;
use App\Http\Controllers\DataController;
use Inertia\Inertia;
use Spatie\Permission\Facades\Permission;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes for registration and login
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::post('register', [RegisteredUserController::class, 'store'])->name('register-post');
    Route::get('invite/{token}', [InviteController::class, 'show'])->name('register');
    // Request reset link
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    // Show reset form
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    // Update password
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

// Authenticated routes that require verification
Route::middleware(['auth', 'verified', 'can:view dashboard'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('can:edit profile');
    Route::put('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update')->middleware('can:edit profile');

    Route::get('/dashboard', function () {
        return Inertia::render('Data');
    })->name('dashboard');

    // API routes for data management
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/data', [DataController::class, 'listTables'])
            ->middleware('can:view data');
        Route::get('/api/data/{table}', [DataController::class, 'fetchData'])
            ->middleware('can:view data');
    });

    // Individual route permissions
    Route::post('/api/data/{table}', [DataController::class, 'createRow'])
        ->middleware('can:create data');
    Route::put('/api/data/{table}/{id}', [DataController::class, 'updateRow'])
        ->middleware('can:edit data');
    Route::delete('/api/data/{table}/{id}', [DataController::class, 'deleteRow'])
        ->middleware('can:delete data');

    // Invite creation route
    Route::post('invite/create', [InviteController::class, 'create'])
        ->name('invite.create')
        ->middleware('can:can invite'); // This assumes you have a permission for invite creation
});

/**
 *
 * This file contains the route definitions for the Laravel application.
 * It includes public routes, authentication routes, and authenticated routes
 * that require user verification and specific permissions.
 *
 * Public Routes:
 * - GET /: Renders the welcome page with Laravel and PHP version information.
 *
 * Routes for Registration and Login (Guest Middleware):
 * - GET /login: Displays the login form.
 * - POST /login: Handles the login request.
 * - POST /register: Handles the registration request.
 * - GET /invite/{token}: Displays the registration form for invited users.
 * - POST /forgot-password: Sends a password reset link.
 * - GET /reset-password/{token}: Displays the password reset form.
 * - POST /reset-password: Handles the password reset request.
 *
 * Authenticated Routes (Auth, Verified, and Permission Middleware):
 * - POST /logout: Logs out the authenticated user.
 * - GET /profile: Displays the user's profile.
 * - PUT /profile: Updates the user's profile (requires 'edit profile' permission).
 * - PUT /profile/password/update: Updates the user's password (requires 'edit profile' permission).
 * - GET /dashboard: Renders the dashboard page.
 *
 * API Routes for Data Management (Throttle Middleware):
 * - GET /api/data: Lists available data tables (requires 'view data' permission).
 * - GET /api/data/{table}: Fetches data from a specific table (requires 'view data' permission).
 * - POST /api/data/{table}: Creates a new row in a specific table (requires 'create data' permission).
 * - PUT /api/data/{table}/{id}: Updates a row in a specific table (requires 'edit data' permission).
 * - DELETE /api/data/{table}/{id}: Deletes a row in a specific table (requires 'delete data' permission).
 *
 * Invite Creation Route:
 * - POST /invite/create: Creates an invite (requires 'can invite' permission).
 */
