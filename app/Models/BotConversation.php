<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $telegram_chat_id
 * @property string $step
 * @property array|null $data
 * @property Carbon|null $expires_at
 */
class BotConversation extends Model
{
    protected $fillable = [
        'telegram_chat_id',
        'step',
        'data',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Retrieve active (non-expired) conversation by chat ID */
    public static function active(string|int $chatId): ?self
    {
        return self::where('telegram_chat_id', (string) $chatId)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    /** Create or reset a conversation to a new step */
    public static function startOrReplace(string|int $chatId, string $step, array $data = []): self
    {
        return self::updateOrCreate(
            ['telegram_chat_id' => (string) $chatId],
            [
                'step' => $step,
                'data' => $data,
                'expires_at' => now()->addMinutes(30),
            ]
        );
    }

    /** Clear a conversation once onboarding is done */
    public static function clear(string|int $chatId): void
    {
        self::where('telegram_chat_id', (string) $chatId)->delete();
    }
}
