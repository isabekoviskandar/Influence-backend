<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Calculate base stats BEFORE filtering
        $allChannels = Channel::where('user_id', $user->id)->get();
        $stats = [
            'total_channels' => $allChannels->count(),
            'active_channels' => $allChannels->where('is_active', true)->count(),
            'total_members' => $allChannels->sum('member_count'),
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
            ->with(['stats' => fn ($q) => $q->latest()->limit(30)]);

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
            'engagement_rate' => $ch->engagement_rate,
            'potential_score' => $ch->potential_score,
            'is_active' => $ch->is_active,
            'posts_count' => $ch->posts_count,
            'last_synced_at' => $ch->last_synced_at?->diffForHumans(),
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
