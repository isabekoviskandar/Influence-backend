<?php

namespace App\Services\Bot;

use App\Models\Channel;
use App\Models\Post;
use Illuminate\Support\Facades\Log;

class ChannelPostService
{
    // Telegram sends `channel_post` and `edited_channel_post` updates
    // whenever something is posted or edited in a channel the bot is admin of.
    // We store these so SyncChannelStats has real view data to work with.

    public function handle(array $post, bool $isEdit = false): void
    {
        $chatId = $post['chat']['id'];
        $messageId = $post['message_id'];
        $views = $post['views'] ?? 0;
        $date = $post['date'] ?? now()->timestamp;

        $channel = Channel::where('chat_id', (string) $chatId)->first();

        if (! $channel) {
            // Bot is in a channel we don't have on record yet — ignore
            return;
        }

        Post::updateOrCreate(
            [
                'channel_id' => $channel->id,
                'telegram_post_id' => $messageId,
            ],
            [
                'views' => $views,
                'forwards' => $post['forwards'] ?? 0,
                'reactions' => isset($post['reactions']) ? count($post['reactions']['results'] ?? []) : 0,
            ]
        );

        Log::channel('telegram')->debug('Channel post captured', [
            'channel_id' => $channel->id,
            'message_id' => $messageId,
            'views' => $views,
            'is_edit' => $isEdit,
        ]);
    }
}
