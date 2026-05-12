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
            $sessionPath = $sessionDir.'/bot_session_sync.madeline';
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

                    // Bots cannot call messages.getHistory (BOT_METHOD_INVALID).
                    // If Madeline does not expose top_message, create and delete a
                    // temporary channel post through the Bot API to learn the current
                    // highest message id.
                    if (! $topMessage) {
                        $topMessage = $this->probeTopMessageId($botToken);
                    }

                    $currentMaxId = (int) $topMessage;
                } catch (\Throwable $e) {
                    Log::error('Failed to get top message', ['error' => $e->getMessage()]);
                    $this->channel->update([
                        'sync_status' => 'failed',
                        'sync_error' => $e->getMessage(),
                    ]);

                    return;
                }

                if ($currentMaxId === 0) {
                    $error = 'Unable to determine top message id. The channel may be empty, or the bot cannot post/delete probe messages.';
                    Log::error($error, ['channel_id' => $this->channel->id]);
                    $this->channel->update([
                        'sync_status' => 'failed',
                        'sync_error' => $error,
                    ]);

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

            $syncedCount = $currentMaxId - $batchStart + 1;
            $this->channel->update([
                'sync_current' => min($currentMaxId, $syncedCount),
            ]);

            if ($batchStart > 1) {
                self::dispatch($this->channel, $batchStart - 1, $currentMaxId)
                    ->delay(now()->addSeconds(10))
                    ->onQueue('default');
            } else {
                $this->channel->update(['sync_status' => 'completed']);
                SyncChannelStats::dispatch($this->channel->fresh())->onQueue('default');
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

    private function probeTopMessageId(string $botToken): int
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $this->channel->chat_id,
                'text' => '.',
                'disable_notification' => true,
            ])->json();

            if (! ($response['ok'] ?? false)) {
                Log::warning('Bot API top message probe was rejected', [
                    'channel_id' => $this->channel->id,
                    'description' => $response['description'] ?? null,
                ]);

                return 0;
            }

            $messageId = (int) ($response['result']['message_id'] ?? 0);

            if ($messageId > 0) {
                Http::post("https://api.telegram.org/bot{$botToken}/deleteMessage", [
                    'chat_id' => $this->channel->chat_id,
                    'message_id' => $messageId,
                ]);
            }

            return $messageId;
        } catch (\Throwable $e) {
            Log::warning('Failed to probe top message with Bot API', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
