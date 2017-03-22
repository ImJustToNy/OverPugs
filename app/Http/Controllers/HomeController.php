<?php

namespace OverwatchLounge\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
    {
        if (Auth::check()) {
            $frontendParametrs = Auth::user()->select(['tag', 'prefered_region', 'us_profile', 'eu_profile', 'kr_profile'])->first();
            $matchParametrs = Auth::user()->matches()->where('expireAt', '>', Carbon::now())->first();
        } else {
            $frontendParametrs = null;
            $matchParametrs = null;
        }

        return view('layout', [
            'frontendParametrs' => $frontendParametrs,
            'matchParametrs' => $matchParametrs,
        ]);
    }
}
