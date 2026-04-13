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
                'engagement_rate' => $channel->engagement_rate,
                'potential_score' => $channel->potential_score,
                'is_active' => $channel->is_active,
                'last_synced_at' => $channel->last_synced_at?->diffForHumans(),
            ],
            'posts' => $posts,
            'stats_history' => $statsHistory,
            'period' => $period,
        ]);
    }
}
