<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Services\Bot\ChannelPostService;
use danog\MadelineProto\API;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchHistoricalPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // MadelineProto can be heavy, give it some time
    public $timeout = 300;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Channel $channel,
        public ?int $nextId = null,
        public ?int $maxId = null
    ) {}

    public function handle(ChannelPostService $postService): void
    {
        Log::channel('telegram')->info('Historical sync chunk: '.($this->nextId ?? 'initial'), [
            'channel_id' => $this->channel->id,
            'chat_id' => $this->channel->chat_id,
        ]);

        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');
        $botToken = config('services.telegram.bot_token');

        if (! $apiId || ! $apiHash) {
            Log::channel('telegram')->error('TELEGRAM_API_ID or TELEGRAM_API_HASH missing. Aborting MTProto fetch.');
            SyncChannelStats::dispatch($this->channel)->onQueue('sync');

            return;
        }

        try {
            // Setup MadelineProto settings
            $settings = new Settings;
            $settings->getAppInfo()->setApiId((int) $apiId)->setApiHash($apiHash);
            $settings->getLogger()
                ->setType(Logger::LOGGER_FILE)
                ->setExtra(storage_path('logs/madeline.log'))
                ->setLevel(Logger::LEVEL_FATAL);

            // Set session file per bot to avoid conflicts
            $sessionPath = storage_path('app/private/madeline_bot.madeline');

            // Initialize MTProto Client
            $MadelineProto = new API($sessionPath, $settings);

            // Start up and login using the bot token
            $MadelineProto->botLogin($botToken);

            $intId = (int) $this->channel->chat_id;
            $usernamePeer = $this->channel->username ? '@'.$this->channel->username : null;

            try {
                // Prioritize numeric ID
                $MadelineProto->getInfo($intId);
                $peer = $intId;
            } catch (\Exception $e) {
                if ($usernamePeer) {
                    try {
                        $MadelineProto->getInfo($usernamePeer);
                        $peer = $usernamePeer;
                    } catch (\Exception $e2) {
                        Log::channel('telegram')->warning('Peer resolution failed in historical fetcher (both ID and username)', [
                            'channel_id' => $this->channel->id,
                            'id' => $intId,
                            'username' => $usernamePeer,
                        ]);
                        SyncChannelStats::dispatch($this->channel)->onQueue('sync');

                        return;
                    }
                } else {
                    SyncChannelStats::dispatch($this->channel)->onQueue('sync');

                    return;
                }
            }

            // --- PROGRESS INITIALIZATION ---
            $currentMaxId = $this->maxId;
            if ($currentMaxId === null) {
                // Determine Top ID for the first time
                try {
                    $pwrChat = $MadelineProto->getPwrChat($peer);
                    $currentMaxId = (int) ($pwrChat['top_message'] ?? $pwrChat['read_inbox_max_id'] ?? 0);

                    if ($currentMaxId === 0) {
                        $fullInfo = $MadelineProto->getFullInfo($peer);
                        $currentMaxId = (int) ($fullInfo['Full']['top_message'] ?? 0);
                    }

                    // Fallback to probe
                    if ($currentMaxId === 0) {
                        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                            'chat_id' => $this->channel->chat_id,
                            'text' => '.',
                            'disable_notification' => true,
                        ]);
                        $tempMsg = $response->json();
                        if (isset($tempMsg['result']['message_id'])) {
                            $currentMaxId = (int) $tempMsg['result']['message_id'];
                            Http::post("https://api.telegram.org/bot{$botToken}/deleteMessage", [
                                'chat_id' => $this->channel->chat_id,
                                'message_id' => $currentMaxId,
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::channel('telegram')->debug('ID Probe failure', ['error' => $e->getMessage()]);
                }

                if ($currentMaxId === 0) {
                    Log::channel('telegram')->error('Could not determine Top ID for recursive sync.');
                    SyncChannelStats::dispatch($this->channel)->onQueue('sync');

                    return;
                }

                $this->channel->update([
                    'sync_status' => 'syncing',
                    'sync_total' => $currentMaxId,
                    'sync_current' => 0,
                ]);
            }

            // Target batch
            $targetId = $this->nextId ?? $currentMaxId;
            $batchSize = 150; // Smaller batches for better recursion visibility
            $batchStart = max(1, $targetId - $batchSize + 1);
            $ids = range($batchStart, $targetId);

            Log::channel('telegram')->info("Fetching batch [{$batchStart} - {$targetId}] for channel {$this->channel->id}");

            $messagesResult = $MadelineProto->channels->getMessages([
                'channel' => $this->channel->chat_id,
                'id' => $ids,
            ]);

            $messages = $messagesResult['messages'] ?? [];
            foreach ($messages as $msg) {
                if (! isset($msg['_']) || $msg['_'] !== 'message') {
                    continue;
                }

                $mockWebhookFormat = [
                    'chat' => ['id' => $this->channel->chat_id],
                    'message_id' => $msg['id'],
                    'date' => $msg['date'] ?? now()->timestamp,
                    'text' => $msg['message'] ?? null,
                    'views' => $msg['views'] ?? 0,
                    'forwards' => $msg['forwards'] ?? 0,
                ];

                if (isset($msg['media'])) {
                    $mockWebhookFormat['document'] = ['file_id' => null, 'file_size' => 0];
                }

                if (isset($msg['reactions']['results'])) {
                    $mockWebhookFormat['reactions']['results'] = [];
                    foreach ($msg['reactions']['results'] as $reaction) {
                        $mockWebhookFormat['reactions']['results'][] = ['total_count' => $reaction['count'] ?? 1];
                    }
                }

                $postService->handle($mockWebhookFormat, false);
            }

            // Update Progress in DB
            $syncedCount = $currentMaxId - $batchStart;
            $this->channel->update([
                'sync_current' => min($currentMaxId, $syncedCount),
            ]);

            // Re-dispatch if more exists
            if ($batchStart > 1) {
                self::dispatch($this->channel, $batchStart - 1, $currentMaxId)
                    ->delay(now()->addSeconds(15)) // 15s delay between recursive batches
                    ->onQueue('default');

                Log::channel('telegram')->info("Recursive sync dispatched for next batch starting below {$batchStart}");
            } else {
                $this->channel->update(['sync_status' => 'completed']);
                Log::channel('telegram')->info("Historical sync for channel {$this->channel->id} COMPLETED.");
            }

            // Refresh stats to show latest counts/views in UI
            SyncChannelStats::dispatch($this->channel)->onQueue('sync');

        } catch (\Exception $e) {
            Log::channel('telegram')->warning('Recursive sync chunk failed.', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw if it might be temporary, or just let Sync Stats try to fix UI
            SyncChannelStats::dispatch($this->channel)->onQueue('sync');
            throw $e;
        }
    }
}
