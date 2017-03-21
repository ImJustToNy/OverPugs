<?php

namespace OverwatchLounge\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OverwatchLounge\Match;

class MatchController extends Controller
{
    public function getAvailable()
    {
        return response()->json(Match::where('expireAt', '>', Carbon::now())->with('user')->get());
    }

    public function refreshMatch()
    {
        $match = $this->userMatch();
        $expireAt = Carbon::now()->addMinutes(5);

        $match->expireAt = $expireAt;

        $match->save();

        return response()->json([
            'status' => 'ok',
            'match' => $this->userMatch(),
            'serverTime' => $this->getServerTime(),
        ]);
    }

    public function deleteMatch(Request $request)
    {
        $match = $this->userMatch();

        $match->expireAt = Carbon::now()->addMinutes(-10);
        $match->save();

        return response()->json(['status' => 'ok']);
    }

    public function addMatch(Request $request)
    {
        $this->validate($request, [
            'invitationLink' => ['required', 'regex:/https?:\/\/discord\.gg\/[a-z0-9]{6,}/i'],
            'region' => ['required', 'in:us,eu,kr'],
            'minRank' => ['required', 'between:1,5000'],
            'maxRank' => ['required', 'between:1,5000'],
            'languages' => ['required', 'array', 'between:1,3'],
            'howMuch' => ['required', 'between:1,5'],
        ]);

        if (is_null($request->user()->{$request->region . '_profile'})) {
            return response()->json(['error' => 'You can\'t create game on server where you have no profile'], 400);
        }

        $profile = $request->user()->{$request->region . '_profile'};

        if ($request->minRank > $request->maxRank) {
            return response()->json(['error' => 'Minimum rank must be smaller than Maximum rank'], 400);
        }

        if ($request->minRank < $profile->rank - 1000 || $request->maxRank < $profile->rank - 1000) {
            return response()->json(['error' => 'Mininmum rank and Maximum rank should be in 1000 points range'], 400);
        }

        $match = new Match;

        $match->region = $request->region;
        $match->languages = implode(',', $request->languages);
        $match->howMuch = $request->howMuch;
        $match->minRank = $request->minRank;
        $match->maxRank = $request->maxRank;
        $match->invitationLink = $request->invitationLink;
        $match->expireAt = Carbon::now()->addMinutes(5);

        $createdMatch = $request->user()->matches()->save($match);

        return response()->json(['match' => $this->userMatch(), 'serverTime' => $this->getServerTime()]);
    }

    private function userMatch()
    {
        return Auth::user()->matches()->where('expireAt', '>', Carbon::now())->firstOrFail();
    }

    private function getServerTime()
    {
        return Carbon::now()->toDateTimeString();
    }
}
