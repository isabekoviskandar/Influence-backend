<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Bot\ChannelMemberService;
use App\Services\Bot\ChannelPostService;
use App\Services\Bot\StartCommandService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly Api $telegram,
        private readonly StartCommandService $startCommand,
        private readonly ChannelMemberService $channelMember,
        private readonly ChannelPostService $channelPost,
    ) {}

    public function handle(Request $request): Response
    {
        $update = $request->all();

        Log::channel('telegram')->info('Webhook received', ['update' => $update]);

        try {
            $this->dispatch($update);
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('Webhook error', [
                'error' => $e->getMessage(),
                'update' => $update,
            ]);
        }

        // Always return 200 — Telegram will retry if you don't
        return response('OK', 200);
    }

    private function dispatch(array $update): void
    {
        // Bot added/removed from a channel
        if (isset($update['my_chat_member'])) {
            $this->channelMember->handle($update['my_chat_member']);

            return;
        }

        if (isset($update['channel_post'])) {
            $this->channelPost->handle($update['channel_post']);

            return;
        }

        if (isset($update['edited_channel_post'])) {
            $this->channelPost->handle($update['edited_channel_post'], isEdit: true);

            return;
        }

        // Regular message to the bot (private chat)
        if (isset($update['message'])) {
            $message = $update['message'];
            $text = $message['text'] ?? '';

            if (str_starts_with($text, '/start')) {
                $this->startCommand->handle($message);

                return;
            }

            if (str_starts_with($text, '/status')) {
                $this->handleStatus($message);

                return;
            }

            if (str_starts_with($text, '/help')) {
                $this->handleHelp($message);

                return;
            }

            // Unknown command — send a friendly nudge
            $this->telegram->sendMessage([
                'chat_id' => $message['chat']['id'],
                'text' => "I didn't understand that. Use /help to see what I can do.",
                'parse_mode' => 'HTML',
            ]);
        }
    }

    private function handleStatus(array $message): void
    {
        $chatId = $message['chat']['id'];

        $user = User::where('telegram_chat_id', $message['from']['id'])->first();

        if (! $user) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "You're not connected yet. Please register at <b>influence.uz</b> and link your Telegram account.",
                'parse_mode' => 'HTML',
            ]);

            return;
        }

        $channelCount = $user->channels()->count();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                "👤 <b>{$user->name}</b>",
                '📦 Plan: <b>'.ucfirst($user->plan).'</b>',
                "📡 Channels connected: <b>{$channelCount}</b>",
                '',
                'Open your dashboard: <b>influence.uz/dashboard</b>',
            ]),
        ]);
    }

    private function handleHelp(array $message): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'parse_mode' => 'HTML',
            'text' => implode("\n", [
                '<b>Influence.uz Bot</b>',
                '',
                '/start — Connect your account',
                '/status — See your connected channels',
                '/help — Show this message',
                '',
                'To get analytics, add me as an <b>admin</b> to your Telegram channel.',
                "I'll start tracking it automatically.",
            ]),
        ]);
    }
}
