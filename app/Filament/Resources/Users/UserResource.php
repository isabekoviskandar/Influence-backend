<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profile Information')
                    ->description('Basic account details and contact info.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('username')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                    ]),

                Section::make('Influence & Stats')
                    ->description('Platform-specific metadata and subscription status.')
                    ->columns(2)
                    ->schema([
                        Select::make('plan')
                            ->options([
                                'free' => 'Free',
                                'pro' => 'Pro',
                                'premium' => 'Premium',
                            ])
                            ->required(),
                        FileUpload::make('avatar')
                            ->image()
                            ->directory('avatars')
                            ->circleCropper(),
                        Textarea::make('bio')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('Telegram Connection')
                    ->description('External platform linking details.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('telegram_username')
                            ->prefix('@'),
                        TextInput::make('telegram_chat_id'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label('Profile')
                    ->description(fn (User $record): string => $record->email ?? '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('plan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'premium' => 'success',
                        'pro' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('telegram_username')
                    ->label('Telegram')
                    ->prefix('@')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->options([
                        'free' => 'Free',
                        'pro' => 'Pro',
                        'premium' => 'Premium',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
