<?php

namespace App\Services\Bot;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Api;

class StartCommandService
{
    public function __construct(
        private readonly Api $telegram,
        private readonly OnboardingService $onboarding,
    ) {}

    public function handle(array $message): void
    {
        $telegramUser = $message['from'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '/start';

        // Extract token from "/start TOKEN"
        $parts = explode(' ', $text, 2);
        $token = $parts[1] ?? null;

        // Bypassing cache for special reserved tokens
        if ($token === 'add_channel') {
            $this->handleAddChannelPayload($chatId, $telegramUser);

            return;
        }

        if ($token) {
            $this->handleWithToken($chatId, $telegramUser, $token);
        } else {
            $this->handleWithoutToken($chatId, $telegramUser);
        }
    }

    // User came via deep-link from the dashboard: /start ABC123
    private function handleWithToken(int $chatId, array $telegramUser, string $token): void
    {
        $userId = Cache::get("tg_link_token:{$token}");

        if (! $userId) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => 'This link has <b>expired</b>. Please go back to the dashboard and generate a new one.',
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
                'text' => 'This Telegram account is already linked to another Influence account.',
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
        $dashboardUrl = config('app.url').'/dashboard';

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                "✅ <b>Account connected, {$firstName}!</b>",
                '',
                'Your Telegram is now linked to your Influence account.',
                '',
                "<b>Next step:</b> Add me as an admin to your Telegram channel and I'll start tracking analytics automatically.",
                '',
                'Need help? Use /help',
            ]),
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    ['text' => '🚀 Open Dashboard', 'url' => $dashboardUrl],
                ]],
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
            $token = Str::random(32);
            Cache::put("dashboard_login_token:{$token}", $user->id, now()->addMinutes(15));
            $dashboardUrl = config('app.url')."/magic-login/{$token}";

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => implode("\n", [
                    "👋 Welcome back, <b>{$firstName}</b>!",
                    '',
                    'Your account is already connected.',
                    '',
                    'Use /status to see your channels.',
                ]),
                'reply_markup' => json_encode([
                    'inline_keyboard' => [[
                        ['text' => '🖥️ Open Dashboard (Auto-Login)', 'url' => $dashboardUrl],
                    ]],
                ]),
            ]);

            return;
        }

        // Complete stranger — start the onboarding flow
        $this->onboarding->startOnboarding($chatId, $telegramUser);
    }

    private function handleAddChannelPayload(int $chatId, array $telegramUser): void
    {
        // Check if they're already linked
        $user = User::where('telegram_chat_id', $telegramUser['id'])->first();

        if (! $user) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => implode("\n", [
                    '👋 <b>Welcome!</b>',
                    '',
                    'To start tracking channels, you first need to link your Influence account.',
                    '',
                    'Please go to your <b>Dashboard > Settings</b> and click "Connect Telegram".',
                ]),
            ]);

            return;
        }

        // Already linked — give simple instruction
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                "🚀 <b>Adding a new channel, {$user->name}?</b>",
                '',
                "It's easy! Just follow these steps:",
                '',
                "1. Add me (@{$this->getBotUsername()}) to your channel as an <b>Admin</b>.",
                '2. Ensure I have permission to <b>Post Messages</b> (so I can see the feed).',
                '',
                "Once added, I'll start tracking your channel analytics automatically!",
            ]),
        ]);
    }

    private function getBotUsername(): string
    {
        return config('services.telegram.bot_username', 'publyc_bot');
    }
}
