<?php

namespace OverSearch\Http\Controllers;

use OverSearch\Match;

class MatchController extends Controller
{
    public function getAvailable()
    {
        return response()->json(Match::with('user')->get());
    }
}
