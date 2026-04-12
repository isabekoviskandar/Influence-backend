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

        Log::channel('telegram')->info('SyncChannelStats (Bot API) started', [
            'channel_id' => $this->channel->id,
            'chat_id' => $chatId,
        ]);

        try {
            // 1. Refresh Channel Subscriber Count
            $memberCount = $telegram->getChatMemberCount([
                'chat_id' => $chatId,
            ]);

            // 2. Recalculate metrics based on cached posts (last 100)
            $allRecentPosts = Post::where('channel_id', $this->channel->id)
                ->orderBy('posted_at', 'desc')
                ->limit(100)
                ->get();

            $avgViews = (int) ($allRecentPosts->avg('views') ?? 0);
            $engagementRate = $memberCount > 0 ? round(($avgViews / $memberCount) * 100, 2) : 0;

            $growthPercent = $this->calcGrowthPercent($memberCount);
            $potentialScore = $this->calcPotentialScore($memberCount, $engagementRate, $growthPercent);

            // 3. Save Snapshots for History Charts
            ChannelStat::create([
                'channel_id' => $this->channel->id,
                'member_count' => $memberCount,
                'avg_views' => $avgViews,
                'post_count' => $allRecentPosts->count(),
                'engagement_rate' => $engagementRate,
                'growth_percent' => $growthPercent,
                'potential_score' => $potentialScore,
                'recorded_at' => now(),
            ]);

            // 4. Update the channel's latest metrics
            $this->channel->update([
                'member_count' => $memberCount,
                'avg_views' => $avgViews,
                'engagement_rate' => $engagementRate,
                'potential_score' => $potentialScore,
                'last_synced_at' => now(),
            ]);

            Log::channel('telegram')->info('SyncChannelStats (Bot API) complete', [
                'channel_id' => $this->channel->id,
                'member_count' => $memberCount,
                'avg_views' => $avgViews,
            ]);

        } catch (TelegramSDKException $e) {
            Log::channel('telegram')->error('SyncChannelStats Bot API error', [
                'channel_id' => $this->channel->id,
                'error' => $e->getMessage(),
            ]);

            if (str_contains(strtolower($e->getMessage()), 'kicked') || str_contains(strtolower($e->getMessage()), 'not found')) {
                $this->channel->update(['is_active' => false]);
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
}
