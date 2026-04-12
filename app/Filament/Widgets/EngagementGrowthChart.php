<?php

namespace App\Filament\Widgets;

use App\Models\ChannelStat;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class EngagementGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Views Expansion';

    protected function getData(): array
    {
        $data = ChannelStat::selectRaw('DATE(recorded_at) as date, avg(avg_views) as aggregate')
            ->where('recorded_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Average Channel Views',
                    'data' => $data->pluck('aggregate')->toArray(),
                    'fill' => 'start',
                    'borderColor' => '#3b82f6', // blue line
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
