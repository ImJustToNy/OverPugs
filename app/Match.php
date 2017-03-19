<?php

namespace OverSearch;

use Illuminate\Database\Eloquent\Model;
use OverSearch\User;

class Match extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLanguagesAttribute($value)
    {
        return explode(',', $value);
    }

    public function getAvatarUrlAttribute($value)
    {
        if (is_null($value)) {
            return 'https://hydra-media.cursecdn.com/overwatch.gamepedia.com/2/2a/PI_Overwatch_Logo_White.png';
        } else {
            return $value;
        }
    }
}
