<?php

namespace App\Services;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChannelService
{
    public function getUserChannels(Request $request)
    {
        $user = Auth::user();

        $channels = $user->channels()->get();

        return response()->json([
            'channels' => $channels,
        ]);
    }

    public function getChannelMetrics($channelId)
    {
        $channel = Channel::findOrFail($channelId);
    }
}
