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

/**
 * Class LoginController
 * @package OverPugs\Http\Controllers
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    /**
     * Register middleware rules.
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['refreshProfile']]);
        $this->middleware('guest', ['except' => ['logout', 'loginDiscord', 'endpointDiscord']]);
    }

    /**
     * Build socialite OAuth URL to authenticate via Battle.net.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login()
    {
        return Socialite::with('battlenet')->stateless()->redirect();
    }

    /**
     * First, check if temp_user_id exists (it is being set while we are hitting battle.net's endpoint)
     * if this is true, we will go to discord's OAuth URL.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginDiscord()
    {
        if (!Session::has('temp_user_id') && !Auth::check()) {
            return redirect()->route('login');
        }

        return Socialite::with('discord')->scopes(['identify'])->redirect();
    }

    /**
     * Drop user's session and redirect home.
     *
     * @return \Illuminate\Http\RedirectResponse
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
     * or if it had discord profile hooked up authenticate this user.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
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

        $this->getProfile($user);

        $user->save();

        if (!$user->discord_id) {
            Session::put('temp_user_id', $user->id);

            return redirect()->route('loginDiscord');
        }

        Auth::login($user, true);

        return redirect()->route('home');
    }

    /**
     * Trigger getProfile method on logged user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refreshProfile(Request $request)
    {
        $this->getProfile($request->user());

        return redirect()->route('home');
    }

    /**
     * Discord's endpoint which checks if auth was successful,
     * try to get from temp_user_id
     * assign avatar, nickname and id from discord API
     * and login the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function endpointDiscord(Request $request)
    {
        try {
            $profile = Socialite::driver('discord')->user();
        } catch (Exception $e) {
            return redirect()->route('loginDiscord');
        }

        if (Auth::check()) {
            $user = Auth::user();
        } elseif (Session::has('temp_user_id')) {
            $user = User::findOrFail(Session::get('temp_user_id'));
        } else {
            return redirect()->route('loginDiscord');
        }

        $user->discord_id = $profile->id;
        $user->discord_nickname = $profile->nickname;
        $user->discord_avatar_url = $profile->avatar;

        $user->save();

        Auth::login($user, true);

        return redirect()->route('home');
    }


    /**
     * Download all nessesary informations about specific battlenet profile
     *
     * @param User $user
     * @return void
     */
    private function getProfile(User $user)
    {
        foreach (['us', 'eu', 'kr'] as $region) {
            $dom = new Dom();
            $dom->load('https://playoverwatch.com/en-us/career/pc/'.$region.'/'.str_replace('#', '-', $user->tag));

            try {
                $portrait = $dom->find('.player-portrait')->getAttribute('src');

                $user->prefered_region = $region;
            } catch (Exception $e) {
                $user->{$region.'_profile'} = null;

                break;
            }

            $rank_wrapper = $dom->find('.competitive-rank', 0);

            if (!is_null($rank_wrapper)) {
                $rank = $rank_wrapper->find('.h6', 0)->text;
            } else {
                $rank = 0;
            }

            $user->{$region.'_profile'} = json_encode([
                'rank'       => intval($rank),
                'avatar_url' => $portrait,
            ]);
        }
    }
}
