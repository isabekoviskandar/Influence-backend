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
            $settings->getAppInfo()->setApiId((int) $apiId)->setApiHash($apiHash);
            $settings->getLogger()
                ->setType(Logger::LOGGER_FILE)
                ->setExtra(storage_path('logs/madeline.log'))
                ->setLevel(Logger::LEVEL_FATAL);

            $sessionDir = storage_path('app/telegram');
            if (! file_exists($sessionDir)) {
                mkdir($sessionDir, 0775, true);
            }

            // 🔥 unique session per channel (FIXED)
            $sessionPath = $sessionDir.'/madeline_'.$this->channel->id.'.madeline';

            $MadelineProto = new API($sessionPath, $settings);

            // safer login
            try {
                $MadelineProto->getSelf();
            } catch (\Throwable $e) {
                $MadelineProto->botLogin($botToken);
            }

            $intId = (int) $this->channel->chat_id;
            $usernamePeer = $this->channel->username ? '@'.$this->channel->username : null;

            try {
                $MadelineProto->getInfo($intId);
                $peer = $intId;
            } catch (\Exception $e) {
                if ($usernamePeer) {
                    try {
                        $MadelineProto->getInfo($usernamePeer);
                        $peer = $usernamePeer;
                    } catch (\Exception $e2) {
                        Log::warning('Peer resolve failed', ['channel_id' => $this->channel->id]);

                        return;
                    }
                } else {
                    return;
                }
            }

            $currentMaxId = $this->maxId ?? null;

            if ($currentMaxId === null) {
                try {
                    $pwrChat = $MadelineProto->getPwrChat($peer);
                    $currentMaxId = (int) ($pwrChat['top_message'] ?? 0);
                } catch (\Throwable $e) {
                    Log::error('Failed to get top message', ['error' => $e->getMessage()]);

                    return;
                }

                if ($currentMaxId === 0) {
                    Log::error('Top message is 0');

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
            }

        } catch (\Throwable $e) {
            Log::error('FetchHistoricalPosts failed', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            // ❌ do NOT throw → prevents queue storm
            return;
        }
    }
}
