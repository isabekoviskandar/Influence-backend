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

        $channels = Channel::where('user_id', $user->id)
            ->withCount('posts')
            ->with(['stats' => fn ($q) => $q->latest()->limit(30)])
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
            ]);

        $stats = [
            'total_channels' => $channels->count(),
            'active_channels' => $channels->where('is_active', true)->count(),
            'total_members' => $channels->sum('member_count'),
            'avg_engagement' => $channels->avg('engagement_rate')
                ? round($channels->avg('engagement_rate'), 2)
                : 0,
        ];

        $botUsername = config('services.telegram.bot_username');

        return Inertia::render('Dashboard/Home', [
            'channels' => $channels,
            'stats' => $stats,
            'bot_username' => $botUsername,
        ]);
    }
}
