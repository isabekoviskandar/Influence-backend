<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelStat;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChannelController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $channels = Channel::where('user_id', $user->id)
            ->withCount('posts')
            ->with(['stats' => fn ($q) => $q->latest('recorded_at')->limit(12)])
            ->latest('added_at')
            ->get()
            ->map(fn (Channel $ch) => [
                'id' => $ch->id,
                'title' => $ch->title,
                'username' => $ch->username,
                'member_count' => $ch->member_count,
                'avg_views' => $ch->avg_views,
                'engagement_rate' => $ch->engagement_rate,
                'potential_score' => $ch->potential_score,
                'is_active' => $ch->is_active,
                'posts_count' => $ch->posts_count,
                'last_synced_at' => $ch->last_synced_at?->diffForHumans(),
                'added_at' => $ch->added_at?->format('M d, Y'),
                'sparkline' => $ch->stats->reverse()->pluck('member_count')->values(),
            ]);

        $botUsername = config('services.telegram.bot_username');

        return Inertia::render('Dashboard/Channels', [
            'channels' => $channels,
            'bot_username' => $botUsername,
        ]);
    }

    public function show(Request $request, Channel $channel): Response
    {
        $user = $request->user();
        abort_if($channel->user_id !== $user->id, 403);

        // 1. Fetch Posts for metrics and list
        $allPosts = $channel->posts()->latest('posted_at')->get();
        $recentPosts = $allPosts->take(50);
        /** @var Collection<int, Post> $displaySelection */
        $displaySelection = $recentPosts->take(5);
        $postsForDisplay = $displaySelection->map(fn (Post $p) => [
            'id' => $p->id,
            'text' => $p->text ?? $p->caption ?? '(media only)',
            'media_type' => $p->media_type,
            'views' => $p->views,
            'reactions' => $p->reactions,
            'posted_at_ago' => $p->posted_at?->diffForHumans(),
            'is_above_avg' => ($p->views ?? 0) > $channel->avg_views,
            'ratio' => $channel->avg_views > 0 ? min(round((($p->views ?? 0) / $channel->avg_views) * 100), 200) : 0,
        ]);

        // 2. Calculate Insights Engine
        // Best time to post
        $dayMap = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $heatmap = array_fill(0, 7, array_fill(0, 24, ['v' => 0, 'c' => 0]));
        /** @var Post $p */
        foreach ($allPosts as $p) {
            $d = (int) $p->posted_at->format('N') - 1;
            $h = (int) $p->posted_at->format('H');
            $heatmap[$d][$h]['v'] += $p->views;
            $heatmap[$d][$h]['c']++;
        }

        $bestDay = 'None';
        $bestHour = '00:00';
        $maxAvg = -1;
        foreach ($heatmap as $d => $hours) {
            foreach ($hours as $h => $data) {
                if ($data['c'] > 0) {
                    $avg = $data['v'] / $data['c'];
                    if ($avg > $maxAvg) {
                        $maxAvg = $avg;
                        $bestDay = $dayMap[$d];
                        $bestHour = str_pad((string) $h, 2, '0', STR_PAD_LEFT).':00';
                    }
                }
            }
        }
        $bestHourEnd = str_pad((string) (((int) explode(':', $bestHour)[0] + 2) % 24), 2, '0', STR_PAD_LEFT).':00';

        // Most viewed post
        $topPost = $allPosts->sortByDesc('views')->first();

        // Consistency
        $postsThisWeek = $allPosts->where('posted_at', '>=', now()->subDays(7))->count();

        // Read rate
        $readRate = $channel->member_count > 0 ? round(($channel->avg_views / $channel->member_count) * 100) : 0;

        // 3. Stats History for Chart
        $period = $request->query('period', '30d');
        $maxDays = $user->max_stats_days;
        if ($period === '30d' && $maxDays < 30) {
            $period = '7d';
        }

        $daysRange = match ($period) {
            '7d' => 7, 'all' => $maxDays, default => 30
        };
        if ($daysRange > 1000) {
            $daysRange = 365;
        } // Sanity limit for 'all' display performance

        /** @var Collection<int, ChannelStat> $statsCollection */
        $statsCollection = $channel->stats()
            ->where('recorded_at', '>=', now()->subDays($daysRange))
            ->orderBy('recorded_at', 'asc')
            ->get();

        $statsHistory = $statsCollection->map(fn (ChannelStat $s) => [
            'date' => $s->recorded_at?->format('M d'),
            'member_count' => $s->member_count,
            'avg_views' => $s->avg_views,
        ]);

        // 4. Final Data Assembly
        $avgViewsRecent = $channel->avg_views_recent ?? $channel->avg_views;
        $isViewsDiff = $avgViewsRecent != $channel->avg_views;

        return Inertia::render('Dashboard/Channel', [
            'channel' => [
                'id' => $channel->id,
                'title' => $channel->title,
                'username' => $channel->username,
                'category' => $channel->category ?? 'Uncategorized',
                'member_count' => $channel->member_count,
                'avg_views' => $channel->avg_views,
                'avg_views_recent' => $avgViewsRecent,
                'is_views_diff' => $isViewsDiff,
                'engagement_rate' => round($channel->engagement_rate),
                'potential_score' => $channel->potential_score,
                'is_active' => $channel->is_active,
                'sync_status' => $channel->sync_status,
                'last_synced_at' => $channel->last_synced_at?->diffForHumans(),
                'added_at_formatted' => $channel->added_at?->format('F Y'),
                'total_posts_count' => $allPosts->count(),
                'plan_limit_days' => $maxDays === PHP_INT_MAX ? 'Unlimited' : $maxDays.' days',
            ],
            'insights' => [
                'best_time' => $bestDay.' · '.$bestHour.'–'.$bestHourEnd,
                'top_post_val' => ($topPost instanceof Post ? $topPost->views : 0).' views',
                'top_post_ago' => ($topPost instanceof Post && $topPost->posted_at) ? $topPost->posted_at->diffForHumans() : 'N/A',
                'consistency' => "$postsThisWeek posts this week",
                'read_rate' => $readRate.'%',
            ],
            'posts' => $postsForDisplay,
            'stats_history' => $statsHistory,
            'period' => $period,
        ]);
    }
}
