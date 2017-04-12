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

    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout']]);
    }

    public function login()
    {
        return Socialite::with('battlenet')->stateless()->redirect();
    }

    public function loginDiscord()
    {
        abort_unless(Session::has('temp_user_id'), 404);

        return Socialite::with('discord')->scopes(['identify'])->redirect();
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('home');
    }

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
            $dom->load('https://playoverwatch.com/en-US/career/pc/' . $region . '/' . str_replace('#', '-', $user->tag));

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
                'rank' => $rank,
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
