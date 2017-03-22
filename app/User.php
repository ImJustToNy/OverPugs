<?php

namespace OverwatchLounge;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OverwatchLounge\Match;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['tag', 'bnet_id'];

    protected $hidden = ['remember_token', 'bnet_id'];

    public function matches()
    {
        return $this->hasMany(Match::class);
    }

    public function getPreferedRegionAttribute($value)
    {
        return strtolower($value);
    }

    public function getUsProfileAttribute($value)
    {
        return json_decode($value);
    }

    public function getEuProfileAttribute($value)
    {
        return json_decode($value);
    }

    public function getKrProfileAttribute($value)
    {
        return json_decode($value);
    }

    public function routeNotificationForSlack()
    {
        return env('SLACK_WEBHOOK_URL');
    }
}
