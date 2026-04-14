<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseStatsOverviewWidget
{
    protected static ?int $sort = -2; // Show near the top

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $premiumUsers = User::whereIn('plan', ['pro', 'premium'])->count();
        $newUsers24h = User::where('created_at', '>=', now()->subDay())->count();

        return [
            Stat::make('Platform Users', number_format($totalUsers))
                ->icon('heroicon-o-users')
                ->description('Total registered accounts'),
            Stat::make('Pro & Premium', number_format($premiumUsers))
                ->icon('heroicon-o-star')
                ->description('Active paid subscriptions')
                ->color('success'),
            Stat::make('New Registrations', number_format($newUsers24h))
                ->icon('heroicon-o-user-plus')
                ->description('Joined in the last 24h')
                ->color($newUsers24h > 0 ? 'success' : 'gray'),
        ];
    }
}
