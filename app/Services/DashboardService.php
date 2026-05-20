<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function calculateMetrics()
    {
        $user = Auth::user();

        $channelNetworkSize = Channel::where('user_id', $user->id)->count('member_count');
        $channelsCount = Channel::where('user_id', $user->id)->count();
        $portfolieSize = Channel::where('user_id', $user->id)->sum('potential_score');
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
}
