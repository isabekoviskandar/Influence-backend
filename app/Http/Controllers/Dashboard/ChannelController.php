<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelStat;
use App\Models\Post;
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
        // Ensure the channel belongs to the authenticated user
        abort_if($channel->user_id !== $request->user()->id, 403);

        $posts = $channel->posts()
            ->latest('posted_at')
            ->limit(50)
            ->get()
            ->map(function ($post) {
                /** @var Post $post */
                return [
                    'id' => $post->id,
                    'text' => $post->text ?? $post->caption,
                    'media_type' => $post->media_type,
                    'views' => $post->views,
                    'forwards' => $post->forwards,
                    'reactions' => $post->reactions,
                    'posted_at' => $post->posted_at?->format('M d, H:i'),
                    'posted_at_ago' => $post->posted_at?->diffForHumans(),
                ];
            });

        $period = $request->query('period', '30d');

        $query = $channel->stats()->latest('recorded_at');

        if ($period === '7d') {
            $query->where('recorded_at', '>=', now()->subDays(7));
            $limit = 7 * 24; // Arbitrary safe limit if syncing hourly
        } elseif ($period === '30d') {
            $query->where('recorded_at', '>=', now()->subDays(30));
            $limit = 30 * 24;
        } else {
            // all
            $limit = 1000;
        }

        $statsHistory = $query->limit($limit)
            ->get()
            ->map(function ($s) {
                /** @var ChannelStat $s */
                return [
                    'date' => $s->recorded_at?->format('M d') ?? $s->created_at->format('M d'),
                    'member_count' => $s->member_count,
                    'avg_views' => $s->avg_views,
                ];
            })
            ->reverse()
            ->values();

        return Inertia::render('Dashboard/Channel', [
            'channel' => [
                'id' => $channel->id,
                'title' => $channel->title,
                'username' => $channel->username,
                'member_count' => $channel->member_count,
                'avg_views' => $channel->avg_views,
                'avg_views_recent' => $channel->avg_views_recent,
                'engagement_rate' => $channel->engagement_rate,
                'potential_score' => $channel->potential_score,
                'is_active' => $channel->is_active,
                'sync_status' => $channel->sync_status,
                'sync_current' => $channel->sync_current,
                'sync_total' => $channel->sync_total,
                'last_synced_at' => $channel->last_synced_at?->diffForHumans(),
            ],
            'posts' => $posts,
            'stats_history' => $statsHistory,
            'period' => $period,
        ]);
    }
}
