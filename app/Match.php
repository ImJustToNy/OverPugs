<?php

namespace OverPugs;

use Illuminate\Database\Eloquent\Model;
use OverPugs\User;

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
}
