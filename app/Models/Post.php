<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable =
        [
            'channel_id',
            'telegram_post_id',
            'views',
            'forwards',
            'reactions',
        ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
