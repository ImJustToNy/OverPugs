<?php

namespace OverPugs\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;
use OverPugs\Http\Controllers\Controller;
use OverPugs\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout', 'loginDiscord', 'endpointDiscord']]);
    }

    public function login()
    {
        return Socialite::with('battlenet')->stateless()->redirect();
    }

    public function loginDiscord()
    {
        return Socialite::with('discord')->redirect();
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

        $regions = ['us', 'eu', 'kr'];

        foreach ($regions as $region) {
            $url = 'https://api.lootbox.eu/pc/' . $region . '/' . str_replace('#', '-', $user->tag) . '/profile';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = json_decode(curl_exec($ch));

            if (curl_errno($ch)) {
                throw new Exception('Can\'t retrieve informations from API');
            }

            curl_close($ch);

            if (isset($result->error)) {
                $profile = null;
            } else {
                $profile = json_encode([
                    'rank' => intval($result->data->competitive->rank),
                    'avatar_url' => $result->data->avatar,
                ]);
            }

            $user->{$region . '_profile'} = $profile;
        }

        $user->save();

        Auth::login($user, true);

        return redirect()->route('home');
    }

    public function endpointDiscord()
    {
        try {
            $profile = Socialite::driver('discord')->user();
        } catch (Exception $e) {
            return redirect()->route('loginDiscord');
        }

        dd($profile);
    }
}
