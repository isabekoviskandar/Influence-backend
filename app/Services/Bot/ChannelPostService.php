<?php

namespace App\Services\Bot;

use App\Jobs\DownloadPostMedia;
use App\Models\Channel;
use App\Models\Post;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ChannelPostService
{
    // Maximum file size for media downloads (10 MB)
    private const MAX_MEDIA_SIZE = 10 * 1024 * 1024;

    /**
     * Handle incoming channel_post or edited_channel_post webhook updates.
     * Saves the full post content (text + media) and dispatches a media download job.
     */
    public function handle(array $post, bool $isEdit = false): void
    {
        $chatId = $post['chat']['id'];
        $messageId = $post['message_id'];

        // Fetch the channel directly to avoid Eloquent serialization caching issues.
        // chat_id should be indexed on the channels table for fast lookups.
        $channel = Channel::where('chat_id', (string) $chatId)->first();

        if (! $channel) {
            return;
        }

        // ─── Extract content ────────────────────────────────────────────────

        $text = $post['text'] ?? null;
        $caption = $post['caption'] ?? null;
        $views = $post['views'] ?? 0;
        $forwards = $post['forward_count'] ?? $post['forwards'] ?? 0;
        $reactions = isset($post['reactions'])
            ? $this->countReactions($post['reactions'])
            : 0;

        // Detect media type and extract file_id
        [$mediaType, $fileId, $fileSize] = $this->extractMedia($post);

        // Original post timestamp from Telegram (Unix → Carbon)
        $postedAt = isset($post['date'])
            ? Carbon::createFromTimestamp($post['date'])
            : now();

        // ─── Save or update the post ────────────────────────────────────────

        $savedPost = Post::updateOrCreate(
            [
                'channel_id' => $channel->id,
                'telegram_post_id' => $messageId,
            ],
            [
                'text' => $text,
                'caption' => $caption,
                'media_type' => $mediaType,
                'media_file_id' => $fileId,
                'views' => $views,
                'forwards' => $forwards,
                'reactions' => $reactions,
                'posted_at' => $postedAt,
            ]
        );

        // ─── Dispatch media download (async, won't block webhook) ───────────

        if ($fileId && $this->shouldDownload($fileSize)) {
            DownloadPostMedia::dispatch($savedPost, $fileId)
                ->onQueue('sync');
        }

        Log::channel('telegram')->debug('Channel post captured', [
            'channel_id' => $channel->id,
            'message_id' => $messageId,
            'media_type' => $mediaType,
            'has_text' => ! empty($text),
            'views' => $views,
            'is_edit' => $isEdit,
        ]);
    }

    /**
     * Extract media type and file_id from a Telegram message.
     * Returns [type, file_id, file_size] or [null, null, null].
     *
     * @return array{0: string|null, 1: string|null, 2: int|null}
     */
    private function extractMedia(array $post): array
    {
        // Photo — Telegram sends an array of sizes, pick the largest
        if (isset($post['photo']) && is_array($post['photo'])) {
            $largest = end($post['photo']);

            return [
                'photo',
                $largest['file_id'] ?? null,
                $largest['file_size'] ?? null,
            ];
        }

        // Video
        if (isset($post['video'])) {
            return [
                'video',
                $post['video']['file_id'] ?? null,
                $post['video']['file_size'] ?? null,
            ];
        }

        // Animation (GIF)
        if (isset($post['animation'])) {
            return [
                'animation',
                $post['animation']['file_id'] ?? null,
                $post['animation']['file_size'] ?? null,
            ];
        }

        // Document (PDF, ZIP, etc.)
        if (isset($post['document'])) {
            return [
                'document',
                $post['document']['file_id'] ?? null,
                $post['document']['file_size'] ?? null,
            ];
        }

        // Audio
        if (isset($post['audio'])) {
            return [
                'audio',
                $post['audio']['file_id'] ?? null,
                $post['audio']['file_size'] ?? null,
            ];
        }

        // Voice message
        if (isset($post['voice'])) {
            return [
                'voice',
                $post['voice']['file_id'] ?? null,
                $post['voice']['file_size'] ?? null,
            ];
        }

        // Video note (round video)
        if (isset($post['video_note'])) {
            return [
                'video_note',
                $post['video_note']['file_id'] ?? null,
                $post['video_note']['file_size'] ?? null,
            ];
        }

        // Sticker
        if (isset($post['sticker'])) {
            return [
                'sticker',
                $post['sticker']['file_id'] ?? null,
                $post['sticker']['file_size'] ?? null,
            ];
        }

        return [null, null, null];
    }

    /**
     * Check if we should download based on file size limit.
     */
    private function shouldDownload(?int $fileSize): bool
    {
        // If Telegram didn't report the size, try downloading anyway
        if ($fileSize === null) {
            return true;
        }

        return $fileSize <= self::MAX_MEDIA_SIZE;
    }

    /**
     * Count total reactions from Telegram's reaction structure.
     */
    private function countReactions(array $reactions): int
    {
        $results = $reactions['results'] ?? [];
        $total = 0;

        foreach ($results as $reaction) {
            $total += $reaction['total_count'] ?? 1;
        }

        return $total;
    }
}
