<?php

namespace OverSearch;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['tag', 'bnet_id'];

    protected $hidden = ['remember_token', 'created_at', 'updated_at', 'bnet_id', 'id'];
}
