<?php

namespace App\Services\Bot;

use App\Models\BotConversation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Api;

/**
 * Handles the multi-step onboarding flow for new users who open the bot
 * without a pre-existing account or a token from the web dashboard.
 *
 * Steps:
 *   idle             → send welcome, ask full name
 *   awaiting_name    → save name, ask phone
 *   awaiting_phone   → save phone, create user, show finish
 *   done             → user already registered
 */
class OnboardingService
{
    public function __construct(private readonly Api $telegram) {}

    // ─── Entry Points ────────────────────────────────────────────────────────

    /** Called by StartCommandService for a completely new user */
    public function startOnboarding(int|string $chatId, array $telegramUser): void
    {
        BotConversation::startOrReplace($chatId, 'awaiting_name', [
            'telegram_first_name' => $telegramUser['first_name'] ?? null,
            'telegram_username' => $telegramUser['username'] ?? null,
        ]);

        $firstName = $telegramUser['first_name'] ?? 'there';

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                '👋 <b>Welcome to Influence!</b>',
                '',
                "Hi <b>{$firstName}</b>, let's get you set up in just 2 quick steps.",
                '',
                "📝 <b>Step 1/2</b> — What's your <b>full name</b>?",
            ]),
        ]);
    }

    /** Route any incoming message to the correct step handler */
    public function handleStep(int|string $chatId, array $message): void
    {
        $conversation = BotConversation::active($chatId);

        if (! $conversation) {
            return;
        }

        match ($conversation->step) {
            'awaiting_name' => $this->stepAwaitingName($chatId, $message, $conversation),
            'awaiting_phone' => $this->stepAwaitingPhone($chatId, $message, $conversation),
            default => null,
        };
    }

    // ─── Step Handlers ───────────────────────────────────────────────────────

    private function stepAwaitingName(
        int|string $chatId,
        array $message,
        BotConversation $conversation
    ): void {
        $name = trim($message['text'] ?? '');

        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => '⚠️ Please enter a valid name (2–100 characters).',
            ]);

            return;
        }

        // Advance to next step
        $data = array_merge($conversation->data ?? [], ['full_name' => $name]);

        BotConversation::startOrReplace($chatId, 'awaiting_phone', $data);

        // Build keyboard with "Share Contact" button
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                "✅ Got it, <b>{$name}</b>!",
                '',
                '📱 <b>Step 2/2</b> — Please share your <b>phone number</b>.',
                '',
                'Tap the button below for 1-tap sharing, or type your number (+998901234567).',
            ]),
            'reply_markup' => json_encode([
                'keyboard' => [[
                    [
                        'text' => '📱 Share My Phone Number',
                        'request_contact' => true,
                    ],
                ]],
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
            ]),
        ]);
    }

    private function stepAwaitingPhone(
        int|string $chatId,
        array $message,
        BotConversation $conversation
    ): void {
        // Accept either a shared contact or typed text
        $phone = null;

        if (isset($message['contact']['phone_number'])) {
            $phone = $message['contact']['phone_number'];
        } elseif (isset($message['text'])) {
            $phone = preg_replace('/\s+/', '', $message['text']);
        }

        if (! $phone || ! preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'parse_mode' => 'HTML',
                'text' => '⚠️ That doesn\'t look like a valid phone number. Please try again (e.g. +998901234567).',
            ]);

            return;
        }

        $data = $conversation->data ?? [];
        $fullName = $data['full_name'] ?? ($data['telegram_first_name'] ?? 'User');
        $tgUser = $data['telegram_username'] ?? null;

        // Check if phone already registered
        $existingUser = User::where('phone', $phone)->first();

        if ($existingUser) {
            // Check if this Telegram ID is already used by someone else
            $conflict = User::where('telegram_chat_id', (string) $chatId)
                ->where('id', '!=', $existingUser->id)
                ->first();

            if ($conflict) {
                // Orphan the conflict user (which was likely a dummy account)
                $conflict->update(['telegram_chat_id' => null, 'telegram_username' => null]);
            }

            // Link Telegram to existing account
            $existingUser->update([
                'telegram_chat_id' => (string) $chatId,
                'telegram_username' => $tgUser,
            ]);

            BotConversation::clear($chatId);
            $this->sendWelcomeFinish($chatId, $fullName, $existingUser);

            return;
        }

        // Create new user
        $user = User::create([
            'username' => $tgUser ?? 'user_'.Str::lower(Str::random(6)),
            'phone' => $phone,
            'telegram_chat_id' => (string) $chatId,
            'telegram_username' => $tgUser,
            'password' => Hash::make(Str::random(32)),
            'plan' => 'free',
        ]);

        Log::info('Bot onboarding: user created', ['user_id' => $user->id, 'chat_id' => $chatId]);

        BotConversation::clear($chatId);
        $this->sendWelcomeFinish($chatId, $fullName, $user);
    }

    // ─── Finish Message ──────────────────────────────────────────────────────

    private function sendWelcomeFinish(int|string $chatId, string $name, User $user): void
    {
        $token = Str::random(32);
        Cache::put("dashboard_login_token:{$token}", $user->id, now()->addMinutes(15));

        $dashboardUrl = config('app.url')."/magic-login/{$token}";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                "🎉 <b>You're all set, {$name}!</b>",
                '',
                '✅ Your Influence account is ready.',
                '',
                '📡 <b>Next: Connect your channel</b>',
                'Add me as an <b>admin</b> to any Telegram channel you manage.',
                "I'll automatically start tracking analytics for it.",
                '',
                "🖥️ <b>Your Dashboard:</b>\n".config('app.url').'/dashboard',
            ]),
            'reply_markup' => json_encode([
                'inline_keyboard' => [[
                    [
                        'text' => '🚀 Open Dashboard (Auto-Login)',
                        'url' => $dashboardUrl,
                    ],
                ]],
            ]),
        ]);

        // Remove the keyboard
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => '📌 Use /status to see your channels anytime.',
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);
    }

    /** Check if this chat has an active onboarding session */
    public function hasActiveSession(int|string $chatId): bool
    {
        return BotConversation::active($chatId) !== null;
    }
}
