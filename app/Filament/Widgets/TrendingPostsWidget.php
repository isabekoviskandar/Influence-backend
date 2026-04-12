<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TrendingPostsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Post::query()->orderBy('views', 'desc')->limit(5))
            ->columns([
                TextColumn::make('channel.title')
                    ->label('Channel')
                    ->limit(20),
                TextColumn::make('text')
                    ->label('Post Context')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('views')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('reactions')
                    ->numeric()
                    ->badge(),
                TextColumn::make('posted_at')
                    ->dateTime()
                    ->label('Posted'),
            ])
            ->filters([
                //
            ])
            ->paginated(false);
    }
}
