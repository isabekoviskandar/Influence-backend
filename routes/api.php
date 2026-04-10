<?php

use App\Http\Controllers\Bot\TelegramWebhookController;
use App\Http\Controllers\TelegramLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post(
    '/webhook/telegram/{secret}',
    [TelegramWebhookController::class, 'handle']
)->name('telegram.webhook');

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {

    // Telegram account linking
    Route::get('/telegram/link', [TelegramLinkController::class, 'generateLink']);
    Route::get('/telegram/status', [TelegramLinkController::class, 'status']);
});
