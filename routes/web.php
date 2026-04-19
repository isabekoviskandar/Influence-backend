<?php

use App\Http\Controllers\Auth\Web\MagicLoginController;
use App\Http\Controllers\Auth\Web\WebLoginController;
use App\Http\Controllers\Auth\Web\WebRegisterController;
use App\Http\Controllers\Dashboard\ChannelController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\SettingsController;
use Illuminate\Support\Facades\Route;

// ─── Guest routes ───────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebLoginController::class, 'create'])->name('login');
    Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');

    Route::get('/register', [WebRegisterController::class, 'create'])->name('register');
    Route::post('/register', [WebRegisterController::class, 'store'])->name('register.store');

    Route::get('/magic-login/{token}', [MagicLoginController::class, 'handle'])->name('magic-login');
});

// ─── Authenticated dashboard routes ─────────────────────────────────────────

Route::middleware('auth')->prefix('dashboard')->name('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('');

    Route::get('/channels', [ChannelController::class, 'index'])->name('.channels');
    Route::get('/channels/{channel}', [ChannelController::class, 'show'])->name('.channel');

    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('.analytics');
    Route::get('/posts', [DashboardController::class, 'posts'])->name('.posts');
    Route::get('/leaderboard', [DashboardController::class, 'leaderboard'])->name('.leaderboard');

    Route::get('/settings', [SettingsController::class, 'index'])->name('.settings');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('.settings.update');
    Route::get('/settings/telegram-link', [SettingsController::class, 'refreshTelegramLink'])
        ->name('.settings.telegram-link');
});

Route::post('/logout', [WebLoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ─── Catch-all for Inertia SPA 404 ──────────────────────────────────────────

Route::get('/', function () {
    return view('welcome');
});
