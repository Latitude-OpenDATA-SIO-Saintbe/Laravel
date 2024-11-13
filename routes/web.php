<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DataController;
use Inertia\Inertia;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes for registration and login
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Authenticated routes that require verification
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/dashboard', function () {
        return Inertia::render('Data');
    })->name('dashboard');

    // API routes for data management
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/api/data', [DataController::class, 'listTables']);
        Route::get('/api/data/{table}', [DataController::class, 'fetchData']);
    });    

    Route::post('/api/data/{table}', [DataController::class, 'createRow']);
    Route::put('/api/data/{table}/{id}', [DataController::class, 'updateRow']);
    Route::delete('/api/data/{table}/{id}', [DataController::class, 'deleteRow']);
});