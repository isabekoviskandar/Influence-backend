<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TelegramLinkController extends Controller
{
    // Called from the frontend when user clicks "Connect Telegram"
    // Returns a one-time deep-link URL
    public function generateLink(): JsonResponse
    {
        $user = Auth::user();
        $token = Str::random(32);

        // Store for 15 minutes — enough time for the user to open the bot
        Cache::put("tg_link_token:{$token}", $user->id, now()->addMinutes(15));

        $botUsername = config('services.telegram.bot_username');
        $deepLink = "https://t.me/{$botUsername}?start={$token}";

        return response()->json([
            'url' => $deepLink,
            'expires_in' => 900, // 15 minutes in seconds
        ]);
    }

    // Webhook endpoint for the bot to call after linking
    // (optional — bot calls the backend directly via StartCommandService)
    public function status(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'linked' => ! is_null($user->telegram_chat_id),
            'telegram_username' => $user->telegram_username,
        ]);
    }
}
