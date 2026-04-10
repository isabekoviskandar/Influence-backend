<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $channel_id
 * @property int|null $member_count
 * @property int|null $avg_views
 * @property int|null $post_count
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
        ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }
}
