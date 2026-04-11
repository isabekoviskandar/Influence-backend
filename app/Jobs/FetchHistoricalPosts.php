<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Services\Bot\ChannelPostService;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchHistoricalPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // MadelineProto can be heavy, give it some time
    public $timeout = 300;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Channel $channel
    ) {}

    public function handle(ChannelPostService $postService): void
    {
        Log::channel('telegram')->info('Starting historical post fetch via MTProto', [
            'channel_id' => $this->channel->id,
            'chat_id' => $this->channel->chat_id,
        ]);

        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');
        $botToken = config('services.telegram.bot_token');

        if (! $apiId || ! $apiHash) {
            Log::channel('telegram')->error('TELEGRAM_API_ID or TELEGRAM_API_HASH missing. Aborting MTProto fetch.');

            // Fallback to normal behavior even if MTProto fails
            SyncChannelStats::dispatch($this->channel)->onQueue('sync');

            return;
        }

        try {
            // Setup MadelineProto settings
            $settings = new Settings;
            $settings->getAppInfo()->setApiId((int) $apiId)->setApiHash($apiHash);

            // Set session file per bot to avoid conflicts
            $sessionPath = storage_path('app/private/madeline_bot.madeline');

            // Initialize MTProto Client
            $MadelineProto = new API($sessionPath, $settings);

            // Start up and login using the bot token
            $MadelineProto->botLogin($botToken);

            try {
                // To fetch history on MTProto, Madeline needs the peer's access_hash.
                // We try to pull info. If it's a private channel and we missed the MTProto update
                // because of HTTP webhooks, it will throw PeerNotInDbException.
                $MadelineProto->getInfo((int) $this->channel->chat_id);
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'internal peer database')) {
                    Log::channel('telegram')->warning('Private channel history fetch aborted. MTProto lacks access_hash due to Bot API webhook interception.', [
                        'channel_id' => $this->channel->id,
                    ]);

                    // Trigger sync and cleanly exit without crashing
                    SyncChannelStats::dispatch($this->channel)->onQueue('sync');

                    return;
                }
            }

            // Fetch the last 150 posts
            $limit = 150;

            $history = $MadelineProto->messages->getHistory([
                'peer' => $this->channel->chat_id,
                'offset_id' => 0,
                'offset_date' => 0,
                'add_offset' => 0,
                'limit' => $limit,
                'max_id' => 0,
                'min_id' => 0,
                'hash' => 0,
            ]);

            $messages = $history['messages'] ?? [];
            $count = count($messages);

            Log::channel('telegram')->info("MTProto fetched {$count} historical messages", [
                'channel_id' => $this->channel->id,
            ]);

            // Save posts utilizing our existing service format
            foreach ($messages as $msg) {
                // Ensure it's not a service message
                if (! isset($msg['_']) || $msg['_'] !== 'message') {
                    continue;
                }

                // We mock the format that the Telegram Bot API webhook uses
                // so we can reuse `ChannelPostService->handle()`
                $mockWebhookFormat = [
                    'chat' => ['id' => $this->channel->chat_id],
                    'message_id' => $msg['id'],
                    'date' => $msg['date'] ?? now()->timestamp,
                    'text' => $msg['message'] ?? null,
                    'views' => $msg['views'] ?? 0,
                    'forwards' => $msg['forwards'] ?? 0,
                ];

                // Deal with media formatting adapter from MTProto to BotAPI
                if (isset($msg['media'])) {
                    // Quick map for media types
                    $mediaType = str_replace('messageMedia', '', $msg['media']['_'] ?? '');
                    $typeLower = strtolower($mediaType);

                    if (in_array($typeLower, ['photo', 'document'])) {
                        // The existing ChannelPostService `extractMedia` expects Bot API arrays.
                        // We will inject a raw dummy object to trigger DownloadPostMedia
                        // if we want to download historical posts, or we skip media for history.
                        // For MVP: let's inject a fake 'document' to pass the format parser

                        // Wait: MadelineProto has a built-in download feature for MTProto,
                        // but `DownloadPostMedia` uses the standard HTTP Bot API!
                        // To allow the standard API to fetch it, we'd need the standard API `file_id`.
                        // MTProto doesn't give a web `file_id` easily, it gives binary file references.
                        // To keep it simple, we don't trigger the HTTP downloader for MTProto backfills.
                        // We simply log the post as having media.
                        $mockWebhookFormat['document'] = [
                            'file_id' => null, // Skip download for history
                            'file_size' => 0,
                        ];
                    }
                }

                // Deal with reactions format adapter
                if (isset($msg['reactions']['results'])) {
                    $mockWebhookFormat['reactions']['results'] = [];
                    foreach ($msg['reactions']['results'] as $reaction) {
                        $mockWebhookFormat['reactions']['results'][] = [
                            'total_count' => $reaction['count'] ?? 1,
                        ];
                    }
                }

                // Force it through our existing post saver
                // isEdit=false
                $postService->handle($mockWebhookFormat, false);
            }

            Log::channel('telegram')->info('MTProto historical fetch complete', [
                'channel_id' => $this->channel->id,
            ]);

            // Finally, calculate the stats now that the database has up to 150 posts
            SyncChannelStats::dispatch($this->channel)->onQueue('sync');

        } catch (\Exception $e) {
            Log::channel('telegram')->warning('Historical post fetch failed. Telegram architecture restricts bots from pulling legacy history.', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            // Even if history fails, trigger sync to initialize the basic channel snapshot
            SyncChannelStats::dispatch($this->channel)->onQueue('sync');

            // We do not re-throw the exception so the queue worker doesn't crash on standard Telegram API hard blocks.
            return;
        }
    }
}
