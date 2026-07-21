<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $dashboardService)
    {
        $this->service = $dashboardService;
    }

    public function calculateMetrics()
    {
        return $this->service->calculateMetrics();
    }

    public function getChannelMetrics(Request $request)
    {
        return $this->service->getChannelMetrics($request);
    }
}
