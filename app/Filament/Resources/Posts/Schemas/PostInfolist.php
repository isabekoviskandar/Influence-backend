<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('channel.title')
                    ->label('Channel'),
                TextEntry::make('telegram_post_id'),
                TextEntry::make('text')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('caption')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('media_type')
                    ->placeholder('-'),
                TextEntry::make('media_file_id')
                    ->placeholder('-'),
                TextEntry::make('media_path')
                    ->placeholder('-'),
                TextEntry::make('media_size')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('media_mime_type')
                    ->placeholder('-'),
                TextEntry::make('views')
                    ->numeric(),
                TextEntry::make('forwards')
                    ->numeric(),
                TextEntry::make('reactions')
                    ->numeric(),
                TextEntry::make('posted_at')
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
