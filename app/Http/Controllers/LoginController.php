<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;

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
                'tag' => $login->nickname,
            ],
            [
                'tag' => $login->nickname,
                'token' => $login->token,
            ]
        );

        Auth::login($user, true);

        return redirect()->route('home');
    }
}
