<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $chat_id
 * @property string|null $title
 * @property string|null $username
 * @property int|null $member_count
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
        ];

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
