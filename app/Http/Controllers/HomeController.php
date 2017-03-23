<?php

namespace OverwatchLounge\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            $user = Auth::user()->select(['tag', 'prefered_region', 'us_profile', 'eu_profile', 'kr_profile'])->first();
        } else {
            $user = null;
        }

        return view('layout', [
            'user' => $user,
        ]);
    }
}
