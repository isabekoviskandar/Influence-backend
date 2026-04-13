<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Services\Bot\BotCommandHandler;
use App\Services\Bot\ChannelMemberService;
use App\Services\Bot\ChannelPostService;
use App\Services\Bot\OnboardingService;
use App\Services\Bot\StartCommandService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly StartCommandService $startCommand,
        private readonly ChannelMemberService $channelMember,
        private readonly ChannelPostService $channelPost,
        private readonly BotCommandHandler $commandHandler,
        private readonly OnboardingService $onboarding,
    ) {}

    public function handle(string $secret, Request $request): Response
    {
        if ($secret !== config('services.telegram.webhook_secret')) {
            Log::channel('telegram')->warning('Invalid webhook secret provided');
            abort(403, 'Invalid secret');
        }

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

        // Regular private message to the bot
        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';

            // /start always resets onboarding or links account
            if (str_starts_with($text, '/start')) {
                $this->startCommand->handle($message);

                return;
            }

            // If user is mid-onboarding, route ALL messages (including contacts) to OnboardingService
            if ($this->onboarding->hasActiveSession($chatId)) {
                $this->onboarding->handleStep($chatId, $message);

                return;
            }

            // Extract the first word as the command (e.g., /status, /help)
            $command = explode(' ', $text)[0];
            $this->commandHandler->handle($command, $message);
        }
    }
}
