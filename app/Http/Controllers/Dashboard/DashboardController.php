<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelStat;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Calculate base stats
        $allChannels = Channel::where('user_id', $user->id)->get();
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

        return Inertia::render('Dashboard/Home', [
            'channels' => $channels,
            'stats' => $stats,
            'bot_username' => $botUsername,
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
        $channels = Channel::where('user_id', $user->id)->get();

        // Aggregate stats over time for charts
        $channelIds = $channels->pluck('id');

        $period = $request->query('period', '30d');
        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $statsHistory = ChannelStat::whereIn('channel_id', $channelIds)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->latest('recorded_at')
            ->get()
            ->groupBy(fn ($s) => $s->recorded_at?->format('M d') ?? $s->created_at->format('M d'))
            ->map(fn ($group, $date) => [
                'date' => $date,
                'members' => $group->sum('member_count'),
                'views' => $group->sum('avg_views'),
                'engagement' => round($group->avg('engagement_rate'), 2),
            ])
            ->values()
            ->reverse()
            ->values();

        $summary = [
            'total_members' => $channels->sum('member_count'),
            'total_views' => $channels->sum('avg_views'),
            'avg_engagement' => $channels->avg('engagement_rate') ? round($channels->avg('engagement_rate'), 2) : 0,
            'total_posts' => Post::whereIn('channel_id', $channelIds)->count(),
        ];

        return Inertia::render('Dashboard/Analytics', [
            'stats_history' => $statsHistory,
            'summary' => $summary,
            'period' => $period,
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
