<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('channel_id')
                    ->relationship('channel', 'title')
                    ->required(),
                TextInput::make('telegram_post_id')
                    ->tel()
                    ->required(),
                Textarea::make('text')
                    ->columnSpanFull(),
                Textarea::make('caption')
                    ->columnSpanFull(),
                TextInput::make('media_type'),
                TextInput::make('media_file_id'),
                TextInput::make('media_path'),
                TextInput::make('media_size')
                    ->numeric(),
                TextInput::make('media_mime_type'),
                TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('forwards')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reactions')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('posted_at'),
            ]);
    }
}
