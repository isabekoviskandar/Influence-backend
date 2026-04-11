<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $channel_id
 * @property string $telegram_post_id
 * @property string|null $text
 * @property string|null $caption
 * @property string|null $media_type
 * @property string|null $media_file_id
 * @property string|null $media_path
 * @property int|null $media_size
 * @property string|null $media_mime_type
 * @property int $views
 * @property int $forwards
 * @property int $reactions
 * @property Carbon|null $posted_at
 * @property-read Channel $channel
 * @property-read bool $has_media
 * @property-read string|null $media_url
 */
class Post extends Model
{
    protected $fillable = [
        'channel_id',
        'telegram_post_id',
        'text',
        'caption',
        'media_type',
        'media_file_id',
        'media_path',
        'media_size',
        'media_mime_type',
        'views',
        'forwards',
        'reactions',
        'posted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function getHasMediaAttribute(): bool
    {
        return $this->media_type !== null;
    }

    public function getMediaUrlAttribute(): ?string
    {
        if (! $this->media_path) {
            return null;
        }

        return asset('storage/'.$this->media_path);
    }
}
