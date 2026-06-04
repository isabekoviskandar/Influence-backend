<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChannelService;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    protected $service;

    public function __construct(ChannelService $channelService)
    {
        $this->service = $channelService;
    }

    public function getUserChannels(Request $request)
    {
        return $this->service->getUserChannels($request);
    }
}
