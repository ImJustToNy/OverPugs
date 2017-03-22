<?php

namespace OverwatchLounge\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OverwatchLounge\Events\NewMatch;
use OverwatchLounge\Events\UpdateExpire;
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

        $userMatch = $this->userMatch();

        event(new UpdateExpire($userMatch->toArray()));

        return response()->json([
            'status' => 'ok',
            'match' => $userMatch,
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
            'region' => ['required', 'in:us,eu,kr'],
            'minRank' => ['between:1,5000'],
            'maxRank' => ['between:1,5000'],
            'languages' => ['required', 'array', 'between:1,3'],
            'howMuch' => ['required', 'between:1,5'],
            'description' => ['max:25'],
            'type' => ['required', 'in:comp,qp,custom,brawl'],
        ]);

        if (isset($request->invitationLink) && !preg_match('/https?:\/\/discord\.gg\/[a-z0-9]{6,}/i', $request->invitationLink)) {
            return response()->json(['error' => 'This is not correct discord invitation link'], 400);
        }

        if (is_null($request->user()->{$request->region . '_profile'})) {
            return response()->json(['error' => 'You can\'t create game on server where you have no profile'], 400);
        }

        $profile = $request->user()->{$request->region . '_profile'};

        if ($request->type == 'comp') {
            if ($request->minRank > $request->maxRank) {
                return response()->json(['error' => 'Minimum rank must be smaller than Maximum rank'], 400);
            }

            if (!isset($request->minRank) || !isset($request->maxRank)) {
                return response()->json(['error' => 'You must specify Minimum rank and Maximum rank'], 400);
            }

            if ($request->minRank < $profile->rank - 1000 || $request->maxRank < $profile->rank - 1000) {
                return response()->json(['error' => 'Mininmum rank and Maximum rank should be in 1000 points range'], 400);
            }
        } else {
            if (!isset($request->description) && $request->type != 'comp') {
                return response()->json(['error' => 'You must specify description'], 400);
            }
        }

        if (!is_null(Auth::user()->matches()->where('expireAt', '>', Carbon::now())->first())) {
            return response()->json(['error' => 'You already have ongoing match'], 400);
        }

        $match = new Match;

        $match->type = $request->type;
        $match->description = $request->description;
        $match->region = $request->region;
        $match->languages = implode(',', $request->languages);
        $match->howMuch = $request->howMuch;
        $match->minRank = $request->minRank;
        $match->maxRank = $request->maxRank;
        $match->invitationLink = $request->invitationLink;
        $match->expireAt = Carbon::now()->addMinutes(1);

        $request->user()->matches()->save($match);
        $userMatch = $this->userMatch();

        event(new NewMatch($userMatch->toArray()));

        return response()->json(['match' => $userMatch, 'serverTime' => $this->getServerTime()]);
    }

    private function userMatch()
    {
        return Auth::user()->matches()->with('user')->where('expireAt', '>', Carbon::now())->firstOrFail();
    }

    private function getServerTime()
    {
        return Carbon::now()->toDateTimeString();
    }
}
