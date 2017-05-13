<?php

namespace OverPugs\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Main page's controller
     * If user is logged in, hook up some informations that frontend needs
     * or if it not, just send null to indicate that we are guest.
     *
     * @return View
     */
    public function home()
    {
        if (Auth::check()) {
            $user = collect(Auth::user())->only(['tag', 'prefered_region', 'avatar_url', 'rank', 'discord_nickname', 'discord_avatar_url']);
        } else {
            $user = null;
        }

        return view('layout', [
            'user' => $user,
        ]);
    }
}
