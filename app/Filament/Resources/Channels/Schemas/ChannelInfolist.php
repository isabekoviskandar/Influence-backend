<?php

namespace App\Filament\Resources\Channels\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ChannelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.id')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('chat_id'),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('username')
                    ->placeholder('-'),
                TextEntry::make('member_count')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('avg_views')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('engagement_rate')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('potential_score')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('added_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
