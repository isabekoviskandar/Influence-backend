<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
}
