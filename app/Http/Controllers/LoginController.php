<?php

namespace OverSearch\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;
use OverSearch\Http\Controllers\Controller;
use OverSearch\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login()
    {
        return Socialite::with('battlenet')->stateless()->redirect();
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('home');
    }

    public function endpoint(Request $request)
    {
        $login = Socialite::driver('battlenet')->stateless()->user();

        $user = User::firstOrCreate(
            [
                'bnet_id' => $login->id,
            ],
            [
                'tag' => $login->nickname,
            ]
        );

        $url = 'https://api.lootbox.eu/pc/eu/' . str_replace('#', '-', $user->tag) . '/profile';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = json_decode(curl_exec($ch));

        if (curl_errno($ch)) {
            throw new Exception('Can\'t retrieve informations from API');
        }

        curl_close($ch);

        $user->rank = $result->data->competitive->rank;
        $user->avatar_url = $result->data->avatar;

        $user->save();

        Auth::login($user, true);

        return redirect()->route('home');
    }
}
