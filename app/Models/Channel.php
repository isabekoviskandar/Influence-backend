<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $chat_id
 * @property string|null $title
 * @property string|null $username
 * @property int|null $member_count
 * @property bool $is_active
 * @property int|null $avg_views
 * @property float|null $engagement_rate
 * @property int|null $potential_score
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $added_at
 * @property-read User|null $user
 * @property-read Collection<int, ChannelStat> $stats
 * @property-read Collection<int, Post> $posts
 */
class Channel extends Model
{
    protected $fillable =
        [
            'user_id',
            'chat_id',
            'title',
            'username',
            'member_count',
            'is_active',
            'avg_views',
            'engagement_rate',
            'potential_score',
            'last_synced_at',
            'added_at',
        ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
            'added_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(ChannelStat::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
