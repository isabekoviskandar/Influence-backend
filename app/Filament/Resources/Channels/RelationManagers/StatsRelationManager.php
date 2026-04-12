<?php

namespace App\Filament\Resources\Channels\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatsRelationManager extends RelationManager
{
    protected static string $relationship = 'stats';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('recorded_at')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('recorded_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('recorded_at')
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Date Recorded')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('member_count')
                    ->label('Followers')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('avg_views')
                    ->label('Avg Views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('post_count')
                    ->label('Recent Posts')
                    ->numeric(),
                TextColumn::make('engagement_rate')
                    ->label('ER')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->badge(),
                TextColumn::make('growth_percent')
                    ->label('Growth')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->color(fn (string $state): string => $state < 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
