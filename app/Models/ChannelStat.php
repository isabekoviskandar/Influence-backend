<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $channel_id
 * @property int|null $member_count
 * @property int|null $avg_views
 * @property int|null $post_count
 * @property float $engagement_rate
 * @property float $growth_percent
 * @property int $potential_score
 * @property Carbon|null $recorded_at
 * @property-read Channel $channel
 */
class ChannelStat extends Model
{
    protected $fillable =
        [
            'channel_id',
            'member_count',
            'avg_views',
            'post_count',
            'engagement_rate',
            'growth_percent',
            'potential_score',
            'recorded_at',
        ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
