<?php

namespace App\Filament\Widgets;

use App\Models\ChannelStat;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class EngagementGrowthChart extends ChartWidget
{
    protected static ?int $sort = 0;

    protected ?string $heading = 'Views Expansion';

    protected function getData(): array
    {
        $data = ChannelStat::selectRaw('DATE(recorded_at) as date, avg(avg_views) as aggregate')
            ->where('recorded_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $data->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->format('M d'))
            ->toArray();

        $values = $data->pluck('aggregate')
            ->map(fn ($value) => is_numeric($value) ? (float) $value : 0)
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Average Channel Views',
                    'data' => $values,
                    'fill' => 'start',
                    'borderColor' => '#3b82f6', // blue line
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
