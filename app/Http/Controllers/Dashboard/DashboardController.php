<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelStat;
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
}
