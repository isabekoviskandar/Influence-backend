<?php

namespace App\Jobs\Bot;

use App\Services\Bot\BotCommandHandler;
use App\Services\Bot\ChannelMemberService;
use App\Services\Bot\ChannelPostService;
use App\Services\Bot\OnboardingService;
use App\Services\Bot\StartCommandService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessTelegramWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $update
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        StartCommandService $startCommand,
        ChannelMemberService $channelMember,
        ChannelPostService $channelPost,
        BotCommandHandler $commandHandler,
        OnboardingService $onboarding,
    ): void {
        try {
            $this->dispatchUpdate($startCommand, $channelMember, $channelPost, $commandHandler, $onboarding);
        } catch (\Throwable $e) {
            Log::channel('telegram')->error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'update' => $this->update,
            ]);

            throw $e;
        }
    }

    private function dispatchUpdate(
        $startCommand,
        $channelMember,
        $channelPost,
        $commandHandler,
        $onboarding
    ): void {
        $update = $this->update;

        // Bot added/removed from a channel
        if (isset($update['my_chat_member'])) {
            $channelMember->handle($update['my_chat_member']);

            return;
        }

        if (isset($update['channel_post'])) {
            $channelPost->handle($update['channel_post']);

            return;
        }

        if (isset($update['edited_channel_post'])) {
            $channelPost->handle($update['edited_channel_post'], isEdit: true);

            return;
        }

        // Regular private message to the bot
        if (isset($update['message'])) {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'] ?? '';

            // /start always resets onboarding or links account
            if (str_starts_with($text, '/start')) {
                $startCommand->handle($message);

                return;
            }

            // If user is mid-onboarding, route ALL messages (including contacts) to OnboardingService
            if ($onboarding->hasActiveSession($chatId)) {
                $onboarding->handleStep($chatId, $message);

                return;
            }

            // Extract the first word as the command (e.g., /status, /help)
            $command = explode(' ', $text)[0];
            $commandHandler->handle($command, $message);
        }
    }
}
