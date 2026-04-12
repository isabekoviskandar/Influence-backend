<?php

namespace App\Filament\Resources\Channels\Tables;

use App\Models\Channel;
use App\Models\ChannelStat;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChannelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Added By')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('username')
                    ->searchable()
                    ->prefix('@')
                    ->color('gray'),
                TextColumn::make('member_count')
                    ->label('Followers')
                    ->numeric()
                    ->sortable()
                    ->badge(),
                TextColumn::make('avg_views')
                    ->label('Avg Views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('engagement_rate')
                    ->label('ER')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->badge(),
                TextColumn::make('potential_score')
                    ->label('Score')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 8 => 'success',
                        $state >= 5 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('growth_trend')
                    ->label('24h Change')
                    ->state(function (Channel $record): string {
                        /** @var ChannelStat|null $lastStat */
                        $lastStat = $record->stats()->where('recorded_at', '<', now()->subDay())->latest('recorded_at')->first();

                        if (! $lastStat || $lastStat->member_count == 0) {
                            return '0%';
                        }
                        $change = (($record->member_count - $lastStat->member_count) / $lastStat->member_count) * 100;

                        return ($change >= 0 ? '+' : '').number_format($change, 1).'%';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_contains($state, '-') ? 'danger' : (str_contains($state, '+') && $state !== '+0.0%' ? 'success' : 'gray')),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('added_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
