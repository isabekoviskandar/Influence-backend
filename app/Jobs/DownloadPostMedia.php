<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class DownloadPostMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    // 10 MB limit in bytes
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    public function __construct(
        public readonly Post $post,
        public readonly string $fileId,
    ) {}

    public function handle(Api $telegram): void
    {
        try {
            // 1. Ask Telegram for the file metadata
            $response = $telegram->getFile(['file_id' => $this->fileId]);
            $filePath = $response->filePath;
            $fileSize = $response->fileSize;

            // 2. Check size limit (10MB)
            if ($fileSize && $fileSize > self::MAX_FILE_SIZE) {
                Log::channel('telegram')->info('Skipping media download: file too large', [
                    'post_id' => $this->post->id,
                    'file_size' => $fileSize,
                    'limit' => self::MAX_FILE_SIZE,
                ]);

                $this->post->update([
                    'media_size' => $fileSize,
                ]);

                return;
            }

            // 3. Build the download URL
            $token = config('services.telegram.bot_token');
            $downloadUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";

            // 4. Download the file
            $response = Http::timeout(60)->get($downloadUrl);

            if (! $response->successful()) {
                Log::channel('telegram')->error('Failed to download media', [
                    'post_id' => $this->post->id,
                    'status' => $response->status(),
                ]);
                throw new \RuntimeException('Media download failed with status '.$response->status());
            }

            // 5. Determine storage path: posts/{channel_id}/{filename}
            $extension = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'bin';
            $storagePath = sprintf(
                'posts/%d/%s.%s',
                $this->post->channel_id,
                $this->post->telegram_post_id,
                $extension
            );

            // 6. Store to public disk
            Storage::disk('public')->put($storagePath, $response->body());

            // 7. Detect MIME type
            $fullPath = Storage::disk('public')->path($storagePath);
            $mimeType = mime_content_type($fullPath) ?: null;

            // 8. Update the post record
            $this->post->update([
                'media_path' => $storagePath,
                'media_size' => strlen($response->body()),
                'media_mime_type' => $mimeType,
            ]);

            Log::channel('telegram')->info('Media downloaded successfully', [
                'post_id' => $this->post->id,
                'path' => $storagePath,
                'size' => strlen($response->body()),
                'mime' => $mimeType,
            ]);

        } catch (TelegramSDKException $e) {
            Log::channel('telegram')->error('Telegram API error during media download', [
                'post_id' => $this->post->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Let the queue retry
        }
    }
}
