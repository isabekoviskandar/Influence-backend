<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Models\ChannelStat;
use App\Models\Post;
use danog\MadelineProto\Logger;
use danog\MadelineProto\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;

class SyncChannelStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry up to 3 times if Telegram API fails
    public int $tries = 3;

    // Wait 60s between retries
    public int $backoff = 60;

    public function __construct(public readonly Channel $channel) {}

    public function handle(Api $telegram): void
    {
        $chatId = $this->channel->chat_id;
        $apiId = config('services.telegram.api_id');
        $apiHash = config('services.telegram.api_hash');
        $botToken = config('services.telegram.bot_token');

        Log::channel('telegram')->info('SyncChannelStats started', [
            'channel_id' => $this->channel->id,
            'chat_id' => $chatId,
        ]);

        // Clear previous error on start
        $this->channel->update(['sync_error' => null]);

        $memberCount = 0;
        $avgViews = 0;
        $engagementRate = 0;

        try {
            // --- Phase 1: Proactive Metrics Refresh (MTProto) ---
            if ($apiId && $apiHash) {
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
                    $MadelineProto = new \danog\MadelineProto\API($sessionDir.'/bot_session_sync.madeline', $settings);
                    try {
                        $MadelineProto->getSelf();
                    } catch (\Throwable $e) {
                        $MadelineProto->botLogin($botToken);
                    }

                    // Peer Discovery: prefer username (more reliable for bots), fallback to numeric ID
                    $usernamePeer = $this->channel->username ? '@'.$this->channel->username : null;
                    $intId = (int) $chatId;
                    $peer = null;

                    try {
                        // First try numeric ID - usually succeeds if bot is already admin
                        $MadelineProto->getInfo($intId);
                        $peer = $intId;
                    } catch (\Throwable $e) {
                        if ($usernamePeer) {
                            Log::channel('telegram')->info('ID resolution failed, trying username fallback', ['peer' => $usernamePeer]);
                            try {
                                $MadelineProto->getInfo($usernamePeer);
                                $peer = $usernamePeer;
                            } catch (\Throwable $e2) {
                                Log::channel('telegram')->warning('Peer resolution failed (both ID and username)', ['id' => $intId, 'username' => $usernamePeer]);
                                throw $e2;
                            }
                        } else {
                            throw $e;
                        }
                    }

                    $pwrChat = $MadelineProto->getPwrChat($peer);
                    $memberCount = $pwrChat['participants_count'] ?? 0;

                    // Determine the highest message ID in this channel:
                    // 1. Check max ID stored in database (numerically via CAST)
                    $maxDbId = (int) Post::where('channel_id', $this->channel->id)
                        ->max(DB::raw('CAST(telegram_post_id AS UNSIGNED)'));

                    // 2. Probe the actual top message ID from Telegram
                    $probedTopId = $this->probeTopMessageId($botToken);

                    $latestId = max($maxDbId, $probedTopId);

                    if ($latestId) {
                        // Refresh the last 200 messages in batches of 100 to keep
                        // views / reactions / forwards up-to-date on older posts too.
                        $batchSize = 100;
                        $refreshFrom = max(1, $latestId - 199); // 200-post window

                        for ($batchStart = $latestId; $batchStart >= $refreshFrom; $batchStart -= $batchSize) {
                            $batchEnd = $batchStart;
                            $batchBegin = max($refreshFrom, $batchStart - $batchSize + 1);
                            $msgIds = range($batchBegin, $batchEnd);

                            $messagesResult = $MadelineProto->channels->getMessages([
                                'channel' => $peer,
                                'id' => $msgIds,
                            ]);

                            if (! isset($messagesResult['messages'])) {
                                continue;
                            }

                            // Pre-fetch existing posts for this batch to avoid 200 individual DB queries
                            $existingPosts = Post::where('channel_id', $this->channel->id)
                                ->whereIn('telegram_post_id', array_map('strval', $msgIds))
                                ->get()
                                ->keyBy('telegram_post_id');

                            foreach ($messagesResult['messages'] as $msg) {
                                if (($msg['_'] ?? '') !== 'message') {
                                    continue;
                                }

                                $views = $msg['views'] ?? 0;
                                $forwards = $msg['forwards'] ?? 0;
                                $reactionsCount = 0;

                                if (isset($msg['reactions']['results'])) {
                                    foreach ($msg['reactions']['results'] as $res) {
                                        $reactionsCount += $res['count'] ?? 0;
                                    }
                                }

                                $text = $msg['message'] ?? null;

                                // Parse media type if present
                                $mediaType = null;
                                if (isset($msg['media']['_'])) {
                                    if ($msg['media']['_'] === 'messageMediaPhoto') {
                                        $mediaType = 'photo';
                                    } elseif ($msg['media']['_'] === 'messageMediaDocument') {
                                        if (isset($msg['media']['document']['mime_type']) && str_starts_with($msg['media']['document']['mime_type'], 'video/')) {
                                            $mediaType = 'video';
                                        } else {
                                            $mediaType = 'document';
                                        }
                                    }
                                }

                                $postedAt = isset($msg['date'])
                                    ? Carbon::createFromTimestamp($msg['date'])
                                    : now();

                                $existing = $existingPosts->get((string) $msg['id']);

                                Post::updateOrCreate(
                                    [
                                        'channel_id' => $this->channel->id,
                                        'telegram_post_id' => (string) $msg['id'],
                                    ],
                                    [
                                        'text' => $text ?? $existing?->text,
                                        'caption' => $existing?->caption,
                                        'media_type' => $mediaType ?? $existing?->media_type,
                                        'views' => $views,
                                        'forwards' => $forwards,
                                        'reactions' => $reactionsCount,
                                        'posted_at' => $postedAt,
                                    ]
                                );
                            }

                            // Brief pause to avoid Telegram API flood limits
                            usleep(300_000); // 300ms
                        }
                    }
                    Log::channel('telegram')->info('MTProto sync successful', ['channel_id' => $this->channel->id]);
                } catch (\Throwable $mtprotoEx) {
                    Log::channel('telegram')->warning('MTProto sync failed, falling back to Bot API', [
                        'channel_id' => $this->channel->id,
                        'error' => $mtprotoEx->getMessage(),
                    ]);
                }
            }

            // --- Phase 2: Fallback / Metrics Calculation (Bot API) ---
            if ($memberCount === 0) {
                $memberCount = $telegram->getChatMemberCount([
                    'chat_id' => $chatId,
                ]);
            }

            // Recalculate metrics based on updated DB data
            // 1. Lifetime Average (All posts synced so far)
            $allPostsQuery = Post::where('channel_id', $this->channel->id);
            $lifetimeAvgViews = (int) ($allPostsQuery->avg('views') ?? 0);
            $totalSyncedPosts = $allPostsQuery->count();

            // 2. Recent Average (Last 100 posts)
            $recentPosts = Post::where('channel_id', $this->channel->id)
                ->orderBy('posted_at', 'desc')
                ->limit(100)
                ->get();
            $recentAvgViews = (int) ($recentPosts->avg('views') ?? 0);

            // 3. Analytics derived from Lifetime data
            $engagementRate = $memberCount > 0 ? round(($lifetimeAvgViews / $memberCount) * 100, 2) : 0;
            $growthPercent = $this->calcGrowthPercent($memberCount);
            $potentialScore = $this->calcPotentialScore($memberCount, $engagementRate, $growthPercent);

            // 3. Save Snapshots
            ChannelStat::create([
                'channel_id' => $this->channel->id,
                'member_count' => $memberCount,
                'avg_views' => $lifetimeAvgViews,
                'avg_views_recent' => $recentAvgViews,
                'post_count' => $totalSyncedPosts,
                'engagement_rate' => $engagementRate,
                'growth_percent' => $growthPercent,
                'potential_score' => $potentialScore,
                'recorded_at' => now(),
            ]);

            // 4. Update the channel's latest metrics
            $this->channel->update([
                'member_count' => $memberCount,
                'avg_views' => $lifetimeAvgViews,
                'avg_views_recent' => $recentAvgViews,
                'engagement_rate' => $engagementRate,
                'potential_score' => $potentialScore,
                'last_synced_at' => now(),
            ]);

            Log::channel('telegram')->info('SyncChannelStats complete (Dual Analytics)', [
                'channel_id' => $this->channel->id,
                'lifetime_avg' => $lifetimeAvgViews,
                'recent_avg' => $recentAvgViews,
                'total_posts' => $totalSyncedPosts,
            ]);

        } catch (TelegramSDKException $e) {
            Log::channel('telegram')->error('SyncChannelStats error', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            if (str_contains(strtolower($e->getMessage()), 'kicked') || str_contains(strtolower($e->getMessage()), 'not found')) {
                $this->channel->update([
                    'is_active' => false,
                    'sync_error' => $e->getMessage(),
                ]);
            } else {
                $this->channel->update(['sync_error' => $e->getMessage()]);
            }

            throw $e;
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function calcGrowthPercent(int $currentCount): float
    {
        // Find the snapshot closest to 7 days ago
        $oldStat = ChannelStat::where('channel_id', $this->channel->id)
            ->where('recorded_at', '<=', now()->subDays(7))
            ->orderBy('recorded_at', 'desc')
            ->first();

        if (! $oldStat || $oldStat->member_count === 0) {
            return 0.0;
        }

        $growth = (($currentCount - $oldStat->member_count) / $oldStat->member_count) * 100;

        return round($growth, 2);
    }

    private function calcPotentialScore(
        int $memberCount,
        float $engagementRate,
        float $growthPercent
    ): int {
        // Each component is normalised to 0–100 then weighted:
        //   Reach (subscribers) — 40%
        //   Engagement rate     — 40%
        //   Growth              — 20%

        // Reach: log scale. 1M+ subscribers = 100 points.
        $reachScore = $memberCount > 0
            ? min(100, (log10($memberCount) / log10(1_000_000)) * 100)
            : 0;

        // Engagement: 10%+ rate = 100 points (Telegram channels avg 5-15%)
        $engagementScore = min(100, ($engagementRate / 10) * 100);

        // Growth: 10%+ weekly growth = 100 points
        $growthScore = min(100, max(0, ($growthPercent / 10) * 100));

        $score = ($reachScore * 0.40)
            + ($engagementScore * 0.40)
            + ($growthScore * 0.20);

        return (int) round($score);
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
            return 0;
        }
    }
}
