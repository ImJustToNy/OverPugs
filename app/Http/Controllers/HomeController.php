<?php

namespace OverwatchLounge\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            $user = Auth::user()->select(['tag', 'prefered_region', 'us_profile', 'eu_profile', 'kr_profile'])->first();
            $matchParametrs = Auth::user()->matches()->where('expireAt', '>', Carbon::now())->first();
        } else {
            $user = null;
            $matchParametrs = null;
        }

        return view('layout', [
            'user' => $user,
            'matchParametrs' => $matchParametrs,
        ]);
    }
}
