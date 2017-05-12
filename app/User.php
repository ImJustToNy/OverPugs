<?php

namespace OverPugs;

use Exception;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPHtmlParser\Dom;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['tag'];

    protected $hidden = ['remember_token'];

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

    /**
     * Download all nessesary informations about specific battlenet profile
     *
     * @return void
     */
    public function getProfile()
    {
        foreach (['us', 'eu', 'kr'] as $region) {
            $dom = new Dom();
            $dom->load('https://playoverwatch.com/en-us/career/pc/'.$region.'/'.str_replace('#', '-', $this->tag));

            try {
                $portrait = $dom->find('.player-portrait')->getAttribute('src');

                $this->prefered_region = $region;
            } catch (Exception $e) {
                $this->{$region.'_profile'} = null;

                break;
            }

            $rank_wrapper = $dom->find('.competitive-rank', 0);

            if (!is_null($rank_wrapper)) {
                $rank = $rank_wrapper->find('.h6', 0)->text;
            } else {
                $rank = 0;
            }

            $this->{$region.'_profile'} = json_encode([
                'rank'       => intval($rank),
                'avatar_url' => $portrait,
            ]);
        }

        $this->save();
    }
}
