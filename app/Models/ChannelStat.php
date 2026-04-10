<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelStat extends Model
{
    protected $fillable =
        [
            'channel_id',
            'member_count',
            'avg_views',
            'post_count',
        ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
