<?php

namespace App\Filament\Widgets;

use App\Models\ChannelStat;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class FollowerGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Follower Growth Chart';

    protected function getData(): array
    {
        $data = ChannelStat::selectRaw('DATE(recorded_at) as date, sum(member_count) as aggregate')
            ->where('recorded_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Network Followers',
                    'data' => $data->pluck('aggregate')->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#10b981', // green line
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
