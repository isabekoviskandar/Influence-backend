<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stats()
    {
        return $this->hasMany(ChannelStat::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
