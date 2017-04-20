<?php

namespace OverPugs\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use OverPugs\Http\Controllers\Controller;
use OverPugs\User;
use PHPHtmlParser\Dom;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    /**
     * Register middleware rules
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout']]);
    }

    /**
     * Build socialite OAuth URL to authenticate via Battle.net
     *
     * @return Battle.net OAuth URL
     */
    public function login()
    {
        return Socialite::with('battlenet')->stateless()->redirect();
    }

    /**
     * First, check if temp_user_id exists (it is being set while we are hitting battle.net's endpoint)
     * if this is true, we will go to discord's OAuth URL
     *
     * @return Discord OAuth URL
     */
    public function loginDiscord()
    {
        if (!Session::has('temp_user_id')) {
            return redirect()->route('login');
        }

        return Socialite::with('discord')->scopes(['identify'])->redirect();
    }

    /**
     * Drop user's session and redirect home
     *
     * @return Redirect home
     */
    public function logout()
    {
        Auth::logout();

        return redirect()->route('home');
    }

    /**
     * Main, battle.net's endpoint
     * We're checking if Auth was successful if not, try once again
     * create user and assign profile by scrapping playoverwatch.com,
     * if we don't have discord profile set up already,
     * set temp_user_id to have reference when hitting discord's endpoint,
     * or if it had discord profile hooked up authenticate this user
     *
     * @param Request $request
     * @return Redirect
     */
    public function endpoint(Request $request)
    {
        try {
            $login = Socialite::driver('battlenet')->stateless()->user();
        } catch (Exception $e) {
            return redirect()->route('login');
        }

        $user = User::firstOrCreate(
            [
                'tag' => $login->nickname,
            ]
        );

        foreach (['us', 'eu', 'kr'] as $region) {
            $dom = new Dom;
            $dom->load('https://playoverwatch.com/en-us/career/pc/' . $region . '/' . str_replace('#', '-', $user->tag));

            try {
                $portrait = $dom->find('.player-portrait')->getAttribute('src');
            } catch (Exception $e) {
                $user->{$region . '_profile'} = null;

                break;
            }

            $rank_wrapper = $dom->find('.competitive-rank', 0);

            if (!is_null($rank_wrapper)) {
                $rank = $rank_wrapper->find('.h6', 0)->text;
            } else {
                $rank = 0;
            }

            $user->{$region . '_profile'} = json_encode([
                'rank' => intval($rank),
                'avatar_url' => $portrait,
            ]);
        }

        $user->save();

        if (!$user->discord_id) {
            Session::put('temp_user_id', $user->id);

            return redirect()->route('loginDiscord');
        }

        Auth::login($user, true);

        return redirect()->route('home');
    }

    /**
     * Discord's endpoint which checks if auth was successful,
     * try to get from temp_user_id
     * assign avatar, nickname and id from discord API
     * and login the user
     *
     * @param Request $request
     * @return Redirect
     */
    public function endpointDiscord(Request $request)
    {
        try {
            $profile = Socialite::driver('discord')->user();
        } catch (Exception $e) {
            return redirect()->route('loginDiscord');
        }

        $user = User::findOrFail(Session::get('temp_user_id'));

        $user->discord_id = $profile->id;
        $user->discord_nickname = $profile->nickname;
        $user->discord_avatar_url = $profile->avatar;

        $user->save();

        Auth::login($user, true);

        return redirect()->route('home');
    }
}
