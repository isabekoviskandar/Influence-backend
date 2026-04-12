<?php

namespace App\Filament\Resources\Channels\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'id'),
                TextInput::make('chat_id')
                    ->required(),
                TextInput::make('title'),
                TextInput::make('username'),
                TextInput::make('member_count')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('avg_views')
                    ->numeric(),
                TextInput::make('engagement_rate')
                    ->numeric(),
                TextInput::make('potential_score')
                    ->numeric(),
                DateTimePicker::make('last_synced_at'),
                DateTimePicker::make('added_at'),
            ]);
    }
}
