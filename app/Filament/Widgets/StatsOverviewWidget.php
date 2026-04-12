<?php

namespace App\Filament\Widgets;

use App\Models\Channel;
use App\Models\ChannelStat;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = Channel::where('is_active', true)->get();
        $totalChannels = $today->count();
        $totalAudience = $today->sum('member_count');
        $avgEngagement = $totalChannels > 0 ? $today->avg('engagement_rate') : 0;

        // Simple Trend Calculation (Yesterday vs Today)
        $yesterdayTotalAudience = ChannelStat::where('recorded_at', '>=', now()->subDays(2))
            ->where('recorded_at', '<', now()->subDay())
            ->sum('member_count');

        $audienceDiff = $totalAudience - $yesterdayTotalAudience;
        $audienceTrend = 0;
        if ($yesterdayTotalAudience > 0) {
            $audienceTrend = ($audienceDiff / $yesterdayTotalAudience) * 100;
        }

        return [
            Stat::make('Tracked Channels', number_format($totalChannels))
                ->icon('heroicon-o-signal')
                ->description('Active channels tracked'),
            Stat::make('Audience Reach', number_format($totalAudience))
                ->icon('heroicon-o-users')
                ->description($audienceTrend >= 0 ? number_format($audienceTrend, 1).'% increase' : number_format(abs($audienceTrend), 1).'% decrease')
                ->descriptionIcon($audienceTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($audienceTrend >= 0 ? 'success' : 'danger'),
            Stat::make('Avg. Engagement', number_format($avgEngagement, 2).'%')
                ->icon('heroicon-o-chart-bar')
                ->description('Platform-wide rate'),
        ];
    }
}
