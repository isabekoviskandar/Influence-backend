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
        Log::channel('telegram')->info('Historical sync chunk: '.($this->nextId ?? 'initial'), [
            'channel_id' => $this->channel->id,
            'chat_id' => $this->channel->chat_id,
        ]);

        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');
        $botToken = config('services.telegram.bot_token');

        if (! $apiId || ! $apiHash || ! $botToken) {
            Log::channel('telegram')->error('Telegram config missing');

            return;
        }

        try {
            $settings = new Settings;
            $settings->getAppInfo()->setApiId((int) $apiId);
            $settings->getAppInfo()->setApiHash($apiHash);
            $settings->getLogger()
                ->setType(Logger::LOGGER_FILE)
                ->setExtra(storage_path('logs/madeline.log'))
                ->setLevel(Logger::LEVEL_FATAL);

            $sessionDir = storage_path('app/telegram');
            if (! file_exists($sessionDir)) {
                mkdir($sessionDir, 0775, true);
            }

            $MadelineProto = new API($sessionDir.'/bot_session_sync.madeline', $settings);
            $MadelineProto->botLogin($botToken);

            $chatId = $this->channel->chat_id;
            $intId = (int) $chatId;
            $usernamePeer = $this->channel->username ? '@'.$this->channel->username : null;
            $peer = null;

            try {
                $MadelineProto->getInfo($intId);
                $peer = $intId;
            } catch (\Exception $e) {
                if ($usernamePeer) {
                    Log::channel('telegram')->info('ID resolution failed, trying username fallback', ['peer' => $usernamePeer]);
                    try {
                        $MadelineProto->getInfo($usernamePeer);
                        $peer = $usernamePeer;
                    } catch (\Exception $e2) {
                        Log::channel('telegram')->error('Peer resolve failed completely', [
                            'channel_id' => $this->channel->id,
                            'error' => $e2->getMessage(),
                        ]);
                        $this->channel->update(['sync_status' => 'failed', 'sync_error' => $e2->getMessage()]);

                        return;
                    }
                } else {
                    $this->channel->update(['sync_status' => 'failed', 'sync_error' => $e->getMessage()]);

                    return;
                }
            }

            $currentMaxId = $this->maxId ?? null;

            if ($currentMaxId === null) {
                try {
                    $pwrChat = $MadelineProto->getPwrChat($peer);
                    $currentMaxId = (int) ($pwrChat['top_message'] ?? 0);
                } catch (\Throwable $e) {
                    Log::channel('telegram')->error('Failed to get top message', ['error' => $e->getMessage()]);

                    return;
                }

                if ($currentMaxId === 0) {
                    Log::channel('telegram')->error('Top message is 0, channel may be empty');

                    return;
                }

                $this->channel->update([
                    'sync_status' => 'syncing',
                    'sync_total' => $currentMaxId,
                    'sync_current' => 0,
                ]);

                Log::channel('telegram')->info('Starting historical sync', [
                    'channel_id' => $this->channel->id,
                    'total_messages' => $currentMaxId,
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
            $saved = 0;

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

                $saved++;
            }

            Log::channel('telegram')->info('Batch saved', [
                'channel_id' => $this->channel->id,
                'batch_start' => $batchStart,
                'batch_end' => $targetId,
                'saved' => $saved,
            ]);

            $syncedCount = $currentMaxId - $batchStart;
            $this->channel->update(['sync_current' => min($currentMaxId, $syncedCount)]);

            if ($batchStart > 1) {
                self::dispatch($this->channel, $batchStart - 1, $currentMaxId)
                    ->delay(now()->addSeconds(10))
                    ->onQueue('default');
            } else {
                $this->channel->update(['sync_status' => 'completed']);
                Log::channel('telegram')->info('Historical sync completed', ['channel_id' => $this->channel->id]);
            }

        } catch (\Throwable $e) {
            Log::channel('telegram')->error('FetchHistoricalPosts failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);
            $this->channel->update(['sync_status' => 'failed', 'sync_error' => $e->getMessage()]);
        }
    }
}
