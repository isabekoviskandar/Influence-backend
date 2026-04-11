<?php

namespace App\Jobs;

use App\Models\Channel;
use App\Models\ChannelStat;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        Log::channel('telegram')->info('SyncChannelStats started', [
            'channel_id' => $this->channel->id,
            'chat_id' => $chatId,
        ]);

        try {
            // 1. Subscriber count
            $memberCount = $telegram->getChatMemberCount([
                'chat_id' => $chatId,
            ]);

            // 2. Recent posts — fetch last 150 messages for robust averages
            $posts = $this->fetchRecentPosts($telegram, $chatId);

            // 3. Calculate metrics
            $avgViews = $this->calcAverageViews($posts);
            $engagementRate = $memberCount > 0
                ? round(($avgViews / $memberCount) * 100, 2)
                : 0;

            // 4. Growth — compare to snapshot from 7 days ago
            $growthPercent = $this->calcGrowthPercent($memberCount);

            // 5. Potential score 0–100
            $potentialScore = $this->calcPotentialScore(
                $memberCount,
                $engagementRate,
                $growthPercent
            );

            // 6. Persist snapshot
            ChannelStat::create([
                'channel_id' => $this->channel->id,
                'member_count' => $memberCount,
                'avg_views' => $avgViews,
                'post_count' => count($posts),
                'engagement_rate' => $engagementRate,
                'growth_percent' => $growthPercent,
                'potential_score' => $potentialScore,
                'recorded_at' => now(),
            ]);

            // 7. Update the channel's denormalised latest stats
            //    so the dashboard doesn't need to join every time
            $this->channel->update([
                'member_count' => $memberCount,
                'avg_views' => $avgViews,
                'engagement_rate' => $engagementRate,
                'potential_score' => $potentialScore,
                'last_synced_at' => now(),
            ]);

            Log::channel('telegram')->info('SyncChannelStats complete', [
                'channel_id' => $this->channel->id,
                'member_count' => $memberCount,
                'avg_views' => $avgViews,
                'engagement_rate' => $engagementRate,
                'growth_percent' => $growthPercent,
                'potential_score' => $potentialScore,
            ]);

        } catch (TelegramSDKException $e) {
            Log::channel('telegram')->error('SyncChannelStats Telegram error', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            // If the bot was kicked or the channel no longer exists, deactivate
            if ($this->isFatalError($e->getMessage())) {
                $this->channel->update(['is_active' => false]);
                $this->fail($e);

                return;
            }

            throw $e; // Let the queue retry
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function fetchRecentPosts(Api $telegram, string $chatId): array
    {
        // Telegram Bot API doesn't have a direct getHistory endpoint.
        // We use getUpdates-style workaround: forward messages from channel
        // via forwardMessage isn't ideal either.
        //
        // Best approach: store posts as they come in via channel_post updates,
        // then query our own posts table. For the initial sync we use
        // getChatHistory via the MTProto API if available, or fall back to
        // what we've cached locally.
        //
        // For MVP: use locally cached posts from the channel_posts table.
        // These get populated by the channel_post webhook update type.

        // Increase limit to 150 to get a broader sample for calculation.
        // It relies on posts already captured via webhook or history sync.
        $posts = Post::where('channel_id', $this->channel->id)
            ->orderBy('created_at', 'desc')
            ->limit(150)
            ->get();

        if ($posts->isEmpty()) {
            Log::channel('telegram')->info('Initial sync: No posts found in database yet. Waiting for channel_post updates.', [
                'channel_id' => $this->channel->id,
            ]);
        }

        return $posts->toArray();
    }

    private function calcAverageViews(array $posts): int
    {
        if (empty($posts)) {
            return 0;
        }

        $totalViews = array_sum(array_column($posts, 'views'));

        return (int) round($totalViews / count($posts));
    }

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

    private function isFatalError(string $message): bool
    {
        $fatalPhrases = [
            'chat not found',
            'bot was kicked',
            'have no rights',
            'bot is not a member',
        ];

        foreach ($fatalPhrases as $phrase) {
            if (str_contains(strtolower($message), $phrase)) {
                return true;
            }
        }

        return false;
    }
}
