<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelStat;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Calculate base stats
        $allChannels = Channel::where('user_id', $user->id)->withCount('posts')->get();
        $totalMembers = $allChannels->sum('member_count');
        $totalViews = $allChannels->sum('avg_views');

        // Calculate Growth (last 24h)
        $yesterday = now()->subHours(24);
        $previousStats = ChannelStat::whereIn('channel_id', $allChannels->pluck('id'))
            ->where('recorded_at', '<', $yesterday)
            ->latest('recorded_at')
            ->get()
            ->unique('channel_id');

        $previousMembers = $previousStats->sum('member_count');
        $memberChange = $previousMembers > 0
            ? round((($totalMembers - $previousMembers) / $previousMembers) * 100, 1)
            : 0;

        $stats = [
            'total_channels' => $allChannels->count(),
            'active_channels' => $allChannels->where('is_active', true)->count(),
            'total_members' => $totalMembers,
            'total_members_change' => $memberChange,
            'avg_engagement' => $allChannels->avg('engagement_rate')
                ? round($allChannels->avg('engagement_rate'), 2)
                : 0,
        ];

        // 2. Fetch Filters
        $search = $request->query('search', '');
        $status = $request->query('status', 'all');
        $sort = $request->query('sort', 'latest');

        // 3. Build Query
        $query = Channel::where('user_id', $user->id)
            ->withCount('posts')
            ->with(['stats' => fn ($q) => $q->latest('recorded_at')->limit(12)]); // For sparklines

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'paused') {
            $query->where('is_active', false);
        }

        switch ($sort) {
            case 'score':
                $query->orderBy('potential_score', 'desc');
                break;
            case 'members':
                $query->orderBy('member_count', 'desc');
                break;
            case 'engagement':
                $query->orderBy('engagement_rate', 'desc');
                break;
            case 'latest':
            default:
                $query->latest('added_at');
                break;
        }

        $channels = $query->get()->map(fn (Channel $ch) => [
            'id' => $ch->id,
            'title' => $ch->title,
            'username' => $ch->username,
            'member_count' => $ch->member_count,
            'avg_views' => $ch->avg_views,
            'avg_views_recent' => $ch->avg_views_recent,
            'engagement_rate' => $ch->engagement_rate,
            'potential_score' => $ch->potential_score,
            'is_active' => $ch->is_active,
            'sync_status' => $ch->sync_status,
            'sync_current' => $ch->sync_current,
            'sync_total' => $ch->sync_total,
            'sync_error' => $ch->sync_error,
            'posts_count' => $ch->posts_count,
            'last_synced_at' => $ch->last_synced_at?->diffForHumans(),
            'sparkline' => $ch->stats->reverse()->pluck('member_count')->values(),
        ]);

        $botUsername = config('services.telegram.bot_username');
        $telegramLink = null;

        if (! $user->telegram_chat_id) {
            $token = Str::random(32);
            Cache::put("tg_link_token:{$token}", $user->id, now()->addMinutes(15));
            $telegramLink = "https://t.me/{$botUsername}?start={$token}";
        }

        return Inertia::render('Dashboard/Home', [
            'channels' => $channels,
            'stats' => $stats,
            'bot_username' => $botUsername,
            'telegram_link' => $telegramLink,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'sort' => $sort,
            ],
        ]);
    }

    public function analytics(Request $request): Response
    {
        $user = $request->user();
        $allChannels = Channel::where('user_id', $user->id)->withCount('posts')->get();

        $channelId = $request->query('channel_id', 'all');
        if ($channelId !== 'all') {
            $channels = $allChannels->where('id', $channelId);
            if ($channels->isEmpty()) {
                $channels = $allChannels;
            }
        } else {
            $channels = $allChannels;
        }

        $channelIds = $channels->pluck('id');

        $period = $request->query('period', '30d');
        $maxDays = $user->max_stats_days;

        if ($period === '90d' && $maxDays < 90) {
            $period = '30d';
        }
        if ($period === '30d' && $maxDays < 30) {
            $period = '7d';
        }

        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        if ($days > $maxDays) {
            $days = $maxDays;
        }

        $now = now();
        $startDate = $now->copy()->subDays($days);
        $previousStartDate = $startDate->copy()->subDays($days);

        // Current period stats
        $statsHistory = ChannelStat::whereIn('channel_id', $channelIds)
            ->where('recorded_at', '>=', $startDate)
            ->latest('recorded_at')
            ->get()
            ->groupBy(fn ($s) => $s->recorded_at?->format('Y-m-d') ?? $s->created_at->format('Y-m-d'));

        // Current period posts
        $currentPosts = Post::whereIn('channel_id', $channelIds)
            ->where('posted_at', '>=', $startDate)
            ->get();

        $currentPostsGrouped = $currentPosts->groupBy(fn ($p) => $p->posted_at->format('Y-m-d'));

        // Map stats history dates filling empty gaps
        $statsData = collect();
        for ($i = $days; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            $group = $statsHistory->get($date) ?? collect();
            $postsGroup = $currentPostsGrouped->get($date) ?? collect();

            $avgView = $group->sum('avg_views');
            $avgEngagement = $group->avg('engagement_rate') ? round($group->avg('engagement_rate')) : 0;
            if ($avgEngagement > 1000) {
                $avgEngagement = 1000;
            }

            $statsData->push([
                'date' => Carbon::parse($date)->format('M d'),
                'members' => $group->sum('member_count'),
                'views' => $avgView,
                'engagement' => $avgEngagement,
                'posts_count' => $postsGroup->count(),
            ]);
        }

        // Previous period stats for variance
        $previousStats = ChannelStat::whereIn('channel_id', $channelIds)
            ->whereBetween('recorded_at', [$previousStartDate, $startDate])
            ->latest('recorded_at')
            ->get();
        $previousPostsCount = Post::whereIn('channel_id', $channelIds)
            ->whereBetween('posted_at', [$previousStartDate, $startDate])
            ->count();

        $curMembers = $allChannels->sum('member_count'); // Use model total instead of potentially empty daily history
        $prevMembers = $previousStats->sortByDesc('recorded_at')->unique('channel_id')->sum('member_count');
        if ($prevMembers <= 0) {
            $prevMembers = $curMembers;
        }

        $curViews = $currentPosts->sum('views');
        if ($curViews <= 0) {
            $curViews = $allChannels->sum('avg_views_recent');
        } // Fallback to current reach if no new posts

        $prevViews = Post::whereIn('channel_id', $channelIds)->whereBetween('posted_at', [$previousStartDate, $startDate])->sum('views');

        $curEngagement = $allChannels->avg('engagement_rate') ?? 0;
        $prevEngagement = $previousStats->avg('engagement_rate') ?? 0;

        $calcChange = function ($cur, $prev) {
            if ($prev <= 0) {
                return $cur > 0 ? 100 : 0;
            }

            return round((($cur - $prev) / $prev) * 100);
        };

        $summary = [
            'total_members' => $curMembers,
            'members_change' => $calcChange($curMembers, $prevMembers),
            'total_views' => $curViews,
            'views_change' => $calcChange($curViews, $prevViews),
            'avg_engagement' => round($curEngagement),
            'engagement_change' => $calcChange($curEngagement, $prevEngagement),
            'total_posts' => $currentPosts->count() ?: $allChannels->sum('posts_count'), // Fallback to total tracked
            'posts_change' => $calcChange($currentPosts->count(), $previousPostsCount),
        ];

        // 24x7 Heatmap Engine
        $heatmapTable = array_fill(0, 7, array_fill(0, 24, ['posts' => 0, 'views' => 0]));
        $dayMap = ['Mon' => 0, 'Tue' => 1, 'Wed' => 2, 'Thu' => 3, 'Fri' => 4, 'Sat' => 5, 'Sun' => 6];
        $revDayMap = array_flip($dayMap);

        foreach ($currentPosts as $post) {
            $day = $post->posted_at->format('D');
            $hour = (int) $post->posted_at->format('H');
            $idx = $dayMap[$day] ?? 0;
            $heatmapTable[$idx][$hour]['posts']++;
            $heatmapTable[$idx][$hour]['views'] += $post->views;
        }

        $flatHeatmap = [];
        $peakList = [];
        foreach ($heatmapTable as $dayIdx => $hours) {
            foreach ($hours as $hour => $data) {
                $avg = $data['posts'] > 0 ? round($data['views'] / $data['posts']) : 0;
                $flatHeatmap[] = [
                    'day' => $revDayMap[$dayIdx],
                    'day_idx' => $dayIdx,
                    'hour' => $hour,
                    'hour_formatted' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00',
                    'posts' => $data['posts'],
                    'avg_views' => $avg,
                    'heat_index' => 0,
                ];
                if ($data['posts'] > 0) {
                    $peakList[] = [
                        'day' => $revDayMap[$dayIdx],
                        'hour' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00',
                        'avg_views' => $avg,
                        'posts' => $data['posts'],
                    ];
                }
            }
        }

        // Heat index (0-4)
        $activeAvgs = collect($flatHeatmap)->filter(fn ($x) => $x['avg_views'] > 0)->pluck('avg_views')->sort()->values();
        foreach ($flatHeatmap as &$cell) {
            $val = $cell['avg_views'];
            if ($val == 0 || $activeAvgs->isEmpty()) {
                $cell['heat_index'] = 0;

                continue;
            }
            $count = $activeAvgs->count();
            if ($count < 3) {
                $cell['heat_index'] = 2;

                continue;
            }
            if ($val >= $activeAvgs->get((int) floor($count * 0.90))) {
                $cell['heat_index'] = 4;
            } elseif ($val >= $activeAvgs->get((int) floor($count * 0.66))) {
                $cell['heat_index'] = 3;
            } elseif ($val >= $activeAvgs->get((int) floor($count * 0.33))) {
                $cell['heat_index'] = 2;
            } else {
                $cell['heat_index'] = 1;
            }
        }

        usort($peakList, fn ($a, $b) => $b['avg_views'] <=> $a['avg_views']);
        $peakChips = array_slice($peakList, 0, 3);

        // Insights Engine
        // 1. Best day to post
        $dayTotals = [];
        foreach ($currentPosts as $p) {
            $d = $p->posted_at->format('l');
            if (! isset($dayTotals[$d])) {
                $dayTotals[$d] = ['views' => 0, 'posts' => 0];
            }
            $dayTotals[$d]['views'] += $p->views;
            $dayTotals[$d]['posts']++;
        }
        $bestDay = 'Not enough data';
        $bestDayAvg = -1;
        foreach ($dayTotals as $d => $data) {
            $a = $data['views'] / $data['posts'];
            if ($a > $bestDayAvg) {
                $bestDayAvg = $a;
                $bestDay = $d;
            }
        }

        // 2. Most consistent hour
        $hourCounts = [];
        foreach ($currentPosts as $p) {
            $h = $p->posted_at->format('H');
            $hourCounts[$h] = ($hourCounts[$h] ?? 0) + 1;
        }
        $mostConsistentHourText = 'Not enough data';
        if (! empty($hourCounts)) {
            arsort($hourCounts);
            $bestH = array_key_first($hourCounts);
            $bestH2 = str_pad((string) ((int) $bestH + 2), 2, '0', STR_PAD_LEFT);
            $mostConsistentHourText = $bestH.':00–'.$bestH2.':00';
        }

        // 3. Highest single post
        $highestPost = $currentPosts->max('views') ?? 0;

        // 4. Posting streak
        $streak = 0;
        $bestStreak = 0;
        $currentStreak = 0;
        for ($i = 0; $i <= $days; $i++) {
            $date = $now->copy()->subDays($i)->format('Y-m-d');
            if (isset($currentPostsGrouped[$date]) && $currentPostsGrouped[$date]->count() > 0) {
                $currentStreak++;
                if ($currentStreak > $bestStreak) {
                    $bestStreak = $currentStreak;
                }
                if ($i <= 1) {
                    $streak = $currentStreak;
                } // Update current streak if active within last 2 days
            } else {
                $currentStreak = 0;
            }
        }

        $insights = [
            'best_day' => $bestDay,
            'consistent_hour' => $mostConsistentHourText,
            'highest_post' => $highestPost,
            'current_streak' => $streak,
            'best_streak' => $bestStreak,
        ];

        $channelSelector = $allChannels->map(fn ($c) => ['id' => (string) $c->id, 'title' => $c->title])->prepend(['id' => 'all', 'title' => 'All Channels']);

        return Inertia::render('Dashboard/Analytics', [
            'stats_history' => $statsData->values(),
            'summary' => $summary,
            'heatmap' => collect($flatHeatmap)->groupBy('day_idx')->values(),
            'peak_chips' => $peakChips,
            'insights' => $insights,
            'period' => $period,
            'user_plan' => $user->plan,
            'max_stats_days' => $maxDays,
            'channels' => $channelSelector,
            'selected_channel' => $channelId,
        ]);
    }

    public function posts(Request $request): Response
    {
        $user = $request->user();
        $channelIds = Channel::where('user_id', $user->id)->pluck('id');

        // Latest 5 posts
        $latest = Post::whereIn('channel_id', $channelIds)
            ->with('channel:id,title,username')
            ->latest('posted_at')
            ->limit(5)
            ->get()
            ->map(fn (Post $p) => [
                'id' => $p->id,
                'text' => $p->text ?? $p->caption ?? '(media only)',
                'media_type' => $p->media_type,
                'views' => $p->views,
                'forwards' => $p->forwards,
                'reactions' => $p->reactions,
                'posted_at' => $p->posted_at?->format('M d, H:i'),
                'posted_at_ago' => $p->posted_at?->diffForHumans(),
                'channel_title' => $p->channel->title,
                'channel_username' => $p->channel->username,
            ]);

        // Most viral 5 posts (by views)
        $viral = Post::whereIn('channel_id', $channelIds)
            ->with('channel:id,title,username')
            ->orderByDesc('views')
            ->limit(5)
            ->get()
            ->map(fn (Post $p) => [
                'id' => $p->id,
                'text' => $p->text ?? $p->caption ?? '(media only)',
                'media_type' => $p->media_type,
                'views' => $p->views,
                'forwards' => $p->forwards,
                'reactions' => $p->reactions,
                'posted_at' => $p->posted_at?->format('M d, H:i'),
                'posted_at_ago' => $p->posted_at?->diffForHumans(),
                'channel_title' => $p->channel->title,
                'channel_username' => $p->channel->username,
            ]);

        return Inertia::render('Dashboard/Posts', [
            'latest_posts' => $latest,
            'viral_posts' => $viral,
        ]);
    }

    public function leaderboard(Request $request): Response
    {
        $user = $request->user();
        $channels = Channel::where('user_id', $user->id)
            ->withCount('posts')
            ->get()
            ->map(fn (Channel $ch) => [
                'id' => $ch->id,
                'title' => $ch->title,
                'username' => $ch->username,
                'member_count' => $ch->member_count,
                'engagement_rate' => $ch->engagement_rate,
                'potential_score' => $ch->potential_score,
                'avg_views' => $ch->avg_views,
                'posts_count' => $ch->posts_count,
                'is_active' => $ch->is_active,
            ])
            ->sortByDesc('potential_score')
            ->values();

        return Inertia::render('Dashboard/Leaderboard', [
            'channels' => $channels,
        ]);
    }
}
