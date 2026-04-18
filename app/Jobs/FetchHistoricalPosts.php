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
use Illuminate\Support\Facades\Log;

class FetchHistoricalPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public int $tries = 3;

    public int $backoff = 60;

    public Channel $channel;

    public ?int $nextId;

    public ?int $maxId;

    public function __construct(Channel $channel, ?int $nextId = null, ?int $maxId = null)
    {
        $this->channel = $channel;
        $this->nextId = $nextId;
        $this->maxId = $maxId;
    }

    public function handle(ChannelPostService $postService): void
    {
        Log::info('Historical sync chunk: '.($this->nextId ?? 'initial'), [
            'channel_id' => $this->channel->id,
            'chat_id' => $this->channel->chat_id,
        ]);

        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');
        $botToken = config('services.telegram.bot_token');

        if (! $apiId || ! $apiHash || ! $botToken) {
            Log::error('Telegram config missing');

            return;
        }

        try {
            $settings = new Settings;
            $settings->getAppInfo()->setApiId((int) $apiId)->setApiHash($apiHash);
            $settings->getLogger()
                ->setType(Logger::LOGGER_FILE)
                ->setExtra(storage_path('logs/madeline.log'))
                ->setLevel(Logger::LEVEL_FATAL);

            $sessionDir = storage_path('app/telegram');
            if (! file_exists($sessionDir)) {
                mkdir($sessionDir, 0775, true);
            }
            $sessionPath = $sessionDir.'/madeline_bot.madeline';
            $MadelineProto = new API($sessionPath, $settings);

            // Login
            try {
                $MadelineProto->getSelf();
            } catch (\Throwable $e) {
                $MadelineProto->botLogin($botToken);
            }

            // Resolve peer — try username first (more reliable for bots)
            $peer = null;
            $usernamePeer = $this->channel->username ? '@'.$this->channel->username : null;

            if ($usernamePeer) {
                try {
                    $MadelineProto->getInfo($usernamePeer);
                    $peer = $usernamePeer;
                } catch (\Throwable $e) {
                    Log::warning('Username peer resolve failed', [
                        'channel_id' => $this->channel->id,
                        'username' => $usernamePeer,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Fallback to numeric ID
            if ($peer === null) {
                try {
                    $intId = (int) $this->channel->chat_id;
                    $MadelineProto->getInfo($intId);
                    $peer = $intId;
                } catch (\Throwable $e) {
                    Log::error('Peer resolve failed completely', [
                        'channel_id' => $this->channel->id,
                        'error' => $e->getMessage(),
                    ]);

                    return;
                }
            }

            $currentMaxId = $this->maxId ?? null;

            if ($currentMaxId === null) {
                try {
                    $fullChannel = $MadelineProto->getInfo($peer);
                    $topMessage = $fullChannel['Chat']['top_message'] ?? null;

                    // fallback: get recent messages to find top ID
                    if (! $topMessage) {
                        $history = $MadelineProto->messages->getHistory([
                            'peer' => $peer,
                            'offset_id' => 0,
                            'offset_date' => 0,
                            'add_offset' => 0,
                            'limit' => 1,
                            'max_id' => 0,
                            'min_id' => 0,
                            'hash' => 0,
                        ]);
                        $topMessage = $history['messages'][0]['id'] ?? 0;
                    }

                    $currentMaxId = (int) $topMessage;
                } catch (\Throwable $e) {
                    Log::error('Failed to get top message', ['error' => $e->getMessage()]);

                    return;
                }

                if ($currentMaxId === 0) {
                    Log::error('Top message is 0, channel may be empty');

                    return;
                }

                $this->channel->update([
                    'sync_status' => 'syncing',
                    'sync_total' => $currentMaxId,
                    'sync_current' => 0,
                ]);
            }

            $targetId = $this->nextId ?? $currentMaxId;
            $batchSize = 100;
            $batchStart = max(1, $targetId - $batchSize + 1);
            $ids = range($batchStart, $targetId);

            $messagesResult = $MadelineProto->channels->getMessages([
                'channel' => $peer,
                'id' => $ids,
            ]);

            $messages = $messagesResult['messages'] ?? [];

            foreach ($messages as $msg) {
                if (! isset($msg['_']) || $msg['_'] !== 'message') {
                    continue;
                }

                $postService->handle([
                    'chat' => ['id' => $this->channel->chat_id],
                    'message_id' => $msg['id'],
                    'date' => $msg['date'] ?? now()->timestamp,
                    'text' => $msg['message'] ?? null,
                    'views' => $msg['views'] ?? 0,
                    'forwards' => $msg['forwards'] ?? 0,
                ], false);
            }

            $syncedCount = $currentMaxId - $batchStart;
            $this->channel->update([
                'sync_current' => min($currentMaxId, $syncedCount),
            ]);

            if ($batchStart > 1) {
                self::dispatch($this->channel, $batchStart - 1, $currentMaxId)
                    ->delay(now()->addSeconds(10))
                    ->onQueue('default');
            } else {
                $this->channel->update(['sync_status' => 'completed']);
                Log::info('Historical sync completed', ['channel_id' => $this->channel->id]);
            }
        } catch (\Throwable $e) {
            Log::error('FetchHistoricalPosts failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->channel->update([
                'sync_status' => 'failed',
                'sync_error' => $e->getMessage(),
            ]);
        }
    }
}
