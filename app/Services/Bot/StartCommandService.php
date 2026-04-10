<?php

namespace App\Services\Bot;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class StartCommandService
{
    public function __construct(private readonly Api $telegram) {}

    public function handle(array $message): void
    {
        $telegramUser = $message['from'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '/start';

        // Extract token from "/start TOKEN"
        $parts = explode(' ', $text, 2);
        $token = $parts[1] ?? null;

        if ($token) {
            $this->handleWithToken($chatId, $telegramUser, $token);
        } else {
            $this->handleWithoutToken($chatId, $telegramUser);
        }
    }

    // User came via deep-link from the dashboard: /start ABC123
    private function handleWithToken(int $chatId, array $telegramUser, string $token): void
    {
        // Token was stored in cache by the web app when user clicked "Connect Telegram"
        // Cache key: "tg_link_token:{token}" → user_id
        $userId = Cache::get("tg_link_token:{$token}");

        if (! $userId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => 'This link has <b>expired</b>. Please go back to influence.uz and generate a new one.',
            ]);

            return;
        }

        $user = User::find($userId);

        if (! $user) {
            Log::warning('StartCommand: user not found for token', ['token' => $token, 'user_id' => $userId]);

            return;
        }

        // Check if this Telegram account is already linked to another user
        $conflict = User::where('telegram_chat_id', $telegramUser['id'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($conflict) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => 'This Telegram account is already linked to another Influence.uz account.',
            ]);

            return;
        }

        // Link the accounts
        $user->update([
            'telegram_chat_id' => $telegramUser['id'],
            'telegram_username' => $telegramUser['username'] ?? null,
        ]);

        // Burn the token — one-time use only
        Cache::forget("tg_link_token:{$token}");

        Log::info('Telegram account linked', ['user_id' => $user->id, 'telegram_id' => $telegramUser['id']]);

        $firstName = $telegramUser['first_name'] ?? $user->name;

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                "✅ <b>Account connected, {$firstName}!</b>",
                '',
                "Your Telegram is now linked to <b>{$user->email}</b>.",
                '',
                "<b>Next step:</b> Add me as an admin to your Telegram channel and I'll start tracking analytics automatically.",
                '',
                'Need help? Use /help',
            ]),
        ]);
    }

    // User opened the bot directly without a token
    private function handleWithoutToken(int $chatId, array $telegramUser): void
    {
        // Check if they're already linked
        $user = User::where('telegram_chat_id', $telegramUser['id'])->first();

        if ($user) {
            $firstName = $telegramUser['first_name'] ?? $user->name;
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => implode("\n", [
                    "👋 Welcome back, <b>{$firstName}</b>!",
                    '',
                    "Your account is already connected to <b>{$user->email}</b>.",
                    '',
                    'Use /status to see your channels or visit <b>influence.uz/dashboard</b>.',
                ]),
            ]);

            return;
        }

        // Complete stranger — send them to register
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                '👋 <b>Welcome to Influence.uz!</b>',
                '',
                'To get started, register at <b>influence.uz</b> and connect your Telegram account from the dashboard.',
                '',
                "Once connected, add me as an admin to your channel and I'll track your analytics automatically.",
            ]),
        ]);
    }
}
