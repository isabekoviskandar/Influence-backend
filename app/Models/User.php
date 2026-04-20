<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string|null $username
 * @property string|null $email
 * @property string|null $telegram_chat_id
 * @property string|null $telegram_username
 * @property string|null $plan
 * @property-read string $name
 * @property-read Collection<int, Channel> $channels
 */
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $fillable =
        [
            'username',
            'email',
            'phone',
            'password',
            'otp',
            'telegram_chat_id',
            'telegram_username',
            'bio',
            'avatar',
            'plan',
        ];

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }

    public function getNameAttribute(): string
    {
        return $this->username
            ?? $this->telegram_username
            ?? $this->email
            ?? 'User';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->email === 'iskandarisabrkov@gmail.com';
    }

    public function getMaxChannelsAttribute(): int
    {
        return match (strtolower($this->plan)) {
            'free' => 1,
            'pro' => 5,
            'premium' => PHP_INT_MAX,
            default => 1,
        };
    }

    public function getMaxStatsDaysAttribute(): int
    {
        return match (strtolower($this->plan)) {
            'free' => 7,
            'pro' => 365,
            'premium' => PHP_INT_MAX,
            default => 7,
        };
    }

    public function getSyncIntervalHoursAttribute(): int
    {
        return match (strtolower($this->plan)) {
            'free' => 24,
            'pro' => 6,
            'premium' => 1,
            default => 24,
        };
    }
}
