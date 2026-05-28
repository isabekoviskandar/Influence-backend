<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Bot\TelegramWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TelegramLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
});

Route::post(
    '/webhook/telegram/{secret}',
    [TelegramWebhookController::class, 'handle']
)->name('telegram.webhook');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', LogoutController::class);
    Route::get('/telegram/link', [TelegramLinkController::class, 'generateLink']);
    Route::get('/telegram/status', [TelegramLinkController::class, 'status']);
});

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('confirm-otp', [AuthController::class, 'confirmOtp']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::get('profile', [UserController::class, 'profile']);
            Route::put('update', [UserController::class, 'update']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('metrics', [DashboardController::class, 'calculateMetrics']);
            Route::get('channels', [DashboardController::class, 'getChannelMetrics']);
        });
    });

    Route::prefix('blog')->group(function () {
        Route::get('list', [BlogController::class, 'index']);
    });

});
