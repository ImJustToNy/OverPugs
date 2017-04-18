<?php

namespace OverPugs\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OverPugs\Events\DeleteMatch;
use OverPugs\Events\NewMatch;
use OverPugs\Events\UpdateExpire;
use OverPugs\Jobs\BuildDiscordNotification;
use OverPugs\Match;
use OverPugs\User;

class MatchController extends Controller
{
    /**
     * Get all available (not expired) matches
     *
     * @return Response
     */
    public function getAvailable()
    {
        return response()->json(Match::where('expireAt', '>', Carbon::now())->with('user')->get());
    }

    /**
     * Refresh existing match that belongs to user
     * by adding 5 minutes to it's expire time
     *
     * @return Response
     */
    public function refreshMatch()
    {
        $match = $this->userMatch();
        $expireAt = Carbon::now()->addMinutes(5);

        $match->expireAt = $expireAt;

        $match->save();

        $userMatch = $this->userMatch();

        event(new UpdateExpire($userMatch));

        return response()->json([
            'status' => 'ok',
            'match' => $userMatch,
        ]);
    }

    /**
     * Remove match (not really) by setting it's expired time -10 minutes
     * which removes it from being selected via getAvailable method
     *
     * @param Request $request
     * @return Response
     */
    public function deleteMatch(Request $request)
    {
        $match = $this->userMatch();

        $match->expireAt = Carbon::now()->addMinutes(-10);
        $match->save();

        event(new DeleteMatch($match->id));

        return response()->json(['status' => 'ok']);
    }

    /**
     * Create a new match with data of user's request,
     * send new event (for real time integration)
     * and deploy a queue job to create a discord notification
     *
     * @param Request $request
     * @return Response
     */
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

        if (isset($request->invitationLink) && !preg_match('/https?:\/\/discord\.gg\/[a-z0-9]{3,}/i', $request->invitationLink)) {
            return response()->json(['error' => 'This is not correct discord invitation link'], 422);
        }

        if (is_null($request->user()->{$request->region . '_profile'})) {
            return response()->json(['error' => 'You can\'t create game on server where you have no profile'], 422);
        }

        $profile = $request->user()->{$request->region . '_profile'};

        if ($request->type == 'comp') {
            if ($request->minRank > $request->maxRank) {
                return response()->json(['error' => 'Minimum rank must be smaller than Maximum rank'], 422);
            }

            if (!isset($request->minRank) || !isset($request->maxRank)) {
                return response()->json(['error' => 'You must specify Minimum rank and Maximum rank'], 422);
            }

            if ($request->minRank < $profile->rank - 1000 || $request->maxRank < $profile->rank - 1000) {
                return response()->json(['error' => 'Mininmum rank and Maximum rank should be in 1000 points range'], 422);
            }
        } else {
            if (!isset($request->description) && $request->type != 'comp') {
                return response()->json(['error' => 'You must specify description'], 422);
            }
        }

        if (!is_null(Auth::user()->matches()->where('expireAt', '>', Carbon::now())->first())) {
            return response()->json(['error' => 'You already have ongoing match'], 422);
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
        $match->expireAt = Carbon::now()->addMinutes(5);

        $request->user()->matches()->save($match);
        $userMatch = $this->userMatch()->toArray();

        event(new NewMatch($userMatch));

        dispatch(new BuildDiscordNotification($match));

        return response()->json(['match' => $userMatch]);
    }

    /**
     * Get match via id in url,
     * which you can get by clicking url in discord message
     * or return 0 which will show an error message in frontend
     *
     * @param Request $request
     * @param type $id
     * @return Response
     */
    public function getMatch(Request $request, $id)
    {
        $match = Match::where('expireAt', '>', Carbon::now())->with('user')->find($id);

        if (is_null($match)) {
            return redirect()->route('home')->with('match', '0');
        }

        return redirect()->route('home')->with('match', $match);
    }

    /**
     * Get first user's match that is not expired
     *
     * @return Collection
     */
    private function userMatch()
    {
        return User::find(Auth::id())->matches()->with('user')->where('expireAt', '>', Carbon::now())->firstOrFail();
    }
}
