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

    /**
     * Download all nessesary informations about specific battlenet profile.
     *
     * @return void
     */
    public function getProfile()
    {
        $played = $portrait = $rank = [];

        foreach (['us', 'eu', 'kr'] as $region) {
            $dom = new Dom();
            $dom->load('https://playoverwatch.com/en-us/career/pc/'.$region.'/'.str_replace('#', '-', $this->tag));

            try {
                $portrait[$region] = $dom->find('.player-portrait')->getAttribute('src');
            } catch (Exception $e) {
                break;
            }

            $rank_wrapper = $dom->find('.competitive-rank', 0);

            if (!is_null($rank_wrapper)) {
                $rank[$region] = $rank_wrapper->find('.h6', 0)->text;
            } else {
                $rank[$region] = 0;
            }

            foreach ($dom->find('td') as $line) {
                if ($line->text == 'Games Played') {
                    $played[$region] = $line->nextSibling()->text;
                    break; // Getting first one
                }
            }
        }

        if (empty($played)) {
            die('Looks like no overwatch profiles were found for this battle.net account. If you believe that\'s an error, please report this.');
        }

        $newest_region = array_search(max($played), $played);

        $this->avatar_url = $portrait[$newest_region];
        $this->rank = $rank[$newest_region];
        $this->prefered_region = $newest_region;

        $this->save();
    }
}
