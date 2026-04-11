<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Bot\TelegramWebhookController;
use App\Http\Controllers\TelegramLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─── Public routes ──────────────────────────────────────────────────────────

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);

// Telegram webhook — secret in URL, no auth middleware
Route::post(
    '/webhook/telegram/{secret}',
    [TelegramWebhookController::class, 'handle']
)->name('telegram.webhook');

// ─── Authenticated routes ───────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    // Current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', LogoutController::class);

    // Telegram account linking
    Route::get('/telegram/link', [TelegramLinkController::class, 'generateLink']);
    Route::get('/telegram/status', [TelegramLinkController::class, 'status']);
});
