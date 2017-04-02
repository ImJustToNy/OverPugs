<?php

namespace OverPugs\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            $user = collect(Auth::user())->only(['tag', 'prefered_region', 'us_profile', 'eu_profile', 'kr_profile', 'discord_nickname'])->toArray();
        } else {
            $user = null;
        }

        return view('layout', [
            'user' => $user,
        ]);
    }
}
