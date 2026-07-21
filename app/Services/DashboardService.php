<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function calculateMetrics()
    {
        $user = Auth::user();

        $channelNetworkSize = Channel::where('user_id', $user->id)->sum('member_count');
        $channelsCount = Channel::where('user_id', $user->id)->count();
        $portfolieSize = Channel::where('user_id', $user->id)->count();
        $averageEngagementRate = Channel::where('user_id', $user->id)->avg('engagement_rate');

        return response()->json(
            [
                'members_count' => $channelNetworkSize,
                'channels_count' => $channelsCount,
                'portfolie_size' => $portfolieSize,
                'average_engagement_rate' => $averageEngagementRate,
            ]
        );
    }

    public function getChannelMetrics(Request $request)
    {
        $data = $request->validate([
            'search' => 'nullable|string',
            'week' => 'nullable|date',
            'day' => 'nullable|date',
            'month' => 'nullable|date',
        ]);

        $query = Channel::query()->where('user_id', Auth::id());
        if (isset($data['search'])) {
            $query->where('name', 'like', "%{$data['search']}%");
        }

        $channels = $query->get();

        $channels->each(function (Channel $channel): void {
            $channel->setAttribute('potential', $channel->potential_score);
            $channel->setAttribute('engagement', $channel->engagement_rate);
        });

        return response()->json([
            'channels' => $channels,
        ]);
    }
}
