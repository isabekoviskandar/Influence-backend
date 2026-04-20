<?php

namespace App\Services\Bot;

use App\Jobs\FetchHistoricalPosts;
use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class ChannelMemberService
{
    public function __construct(private readonly Api $telegram) {}

    public function handle(array $myChatMember): void
    {
        $chat = $myChatMember['chat'];
        $newStatus = $myChatMember['new_chat_member']['status'] ?? null;
        $oldStatus = $myChatMember['old_chat_member']['status'] ?? null;

        // Only care about channels (not groups or private chats)
        if ($chat['type'] !== 'channel') {
            return;
        }

        $isNowAdmin = in_array($newStatus, ['administrator']);
        $wasAdmin = in_array($oldStatus, ['administrator']);
        $isNowRemoved = in_array($newStatus, ['left', 'kicked']);

        Log::channel('telegram')->info('my_chat_member update', [
            'chat_id' => $chat['id'],
            'chat_title' => $chat['title'] ?? null,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        if ($isNowAdmin && ! $wasAdmin) {
            $this->botAddedAsAdmin($chat, $myChatMember);

            return;
        }

        if ($isNowRemoved) {
            $this->botRemoved($chat);

            return;
        }
    }

    private function botAddedAsAdmin(array $chat, array $myChatMember): void
    {
        $chatId = $chat['id'];

        // Try to find who added the bot using the `from` field
        $addedBy = $myChatMember['from'] ?? null;
        $user = null;

        if ($addedBy) {
            $user = User::where('telegram_chat_id', $addedBy['id'])->first();
        }

        // Limit check for new or re-activated channels
        if ($user) {
            $existingChannel = Channel::where('chat_id', (string) $chatId)->first();
            $isNewActivation = ! $existingChannel || ! $existingChannel->is_active;

            if ($isNewActivation) {
                $currentActiveChannels = $user->channels()->where('is_active', true)->count();
                if ($currentActiveChannels >= $user->max_channels) {
                    $this->telegram->sendMessage([
                        'chat_id' => $user->telegram_chat_id,
                        'parse_mode' => 'HTML',
                        'text' => implode("\n", [
                            '🚫 <b>Limit Reached</b>',
                            '',
                            'Your current plan (<b>'.ucfirst($user->plan)."</b>) allows maximum <b>{$user->max_channels}</b> channel(s).",
                            'Please upgrade your plan to add more channels.',
                        ]),
                    ]);

                    return;
                }
            }
        }

        // Upsert the channel record
        $channel = Channel::updateOrCreate(
            ['chat_id' => (string) $chatId],
            [
                'user_id' => $user?->id,
                'title' => $chat['title'] ?? 'Unknown Channel',
                'username' => $chat['username'] ?? null,
                'is_active' => true,
                'added_at' => now(),
            ]
        );

        Log::info('Bot added as admin to channel', [
            'channel_id' => $channel->id,
            'chat_id' => $chatId,
            'user_id' => $user?->id,
        ]);

        // Immediately kick off history fetch, which will then trigger stats sync
        FetchHistoricalPosts::dispatch($channel)->onQueue('default');

        // Notify the owner if we know who they are
        if ($user) {
            $channelName = isset($chat['username'])
                ? "@{$chat['username']}"
                : $chat['title'];

            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_chat_id,
                'parse_mode' => 'HTML',
                'text' => implode("\n", [
                    '📡 <b>Channel connected!</b>',
                    '',
                    "I've been added to <b>{$channelName}</b> as admin.",
                    "I'm pulling your analytics now — check your dashboard in a minute.",
                    '',
                    '<b>influence.uz/dashboard</b>',
                ]),
            ]);
        }
    }

    private function botRemoved(array $chat): void
    {
        $channel = Channel::where('chat_id', (string) $chat['id'])->first();

        if (! $channel) {
            return;
        }

        $channel->update(['is_active' => false]);

        Log::info('Bot removed from channel', ['chat_id' => $chat['id']]);

        // Notify the owner
        $user = $channel->user;
        if ($user?->telegram_chat_id) {
            $channelName = $channel->username
                ? "@{$channel->username}"
                : $channel->title;

            $this->telegram->sendMessage([
                'chat_id' => $user->telegram_chat_id,
                'parse_mode' => 'HTML',
                'text' => implode("\n", [
                    '⚠️ <b>Channel disconnected</b>',
                    '',
                    "I was removed from <b>{$channelName}</b>.",
                    'Analytics tracking has been paused.',
                    '',
                    'To resume, add me back as admin to the channel.',
                ]),
            ]);
        }
    }
}
