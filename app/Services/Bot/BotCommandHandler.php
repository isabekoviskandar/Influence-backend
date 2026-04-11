<?php

namespace App\Services\Bot;

use App\Models\User;
use Telegram\Bot\Api;

class BotCommandHandler
{
    public function __construct(private readonly Api $telegram) {}

    public function handle(string $command, array $message): void
    {
        match ($command) {
            '/status' => $this->handleStatus($message),
            '/help' => $this->handleHelp($message),
            default => $this->handleUnknown($message),
        };
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

    private function handleUnknown(array $message): void
    {
        $this->telegram->sendMessage([
            'chat_id' => $message['chat']['id'],
            'text' => "I didn't understand that. Use /help to see what I can do.",
            'parse_mode' => 'HTML',
        ]);
    }
}
