<?php

namespace OverPugs\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OverPugs\Events\DeleteMatch;
use OverPugs\Events\NewMatch;
use OverPugs\Events\UpdateExpire;
use OverPugs\Match;
use OverPugs\User;
use RestCord\DiscordClient;

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
        ]);
    }

    public function deleteMatch(Request $request)
    {
        $match = $this->userMatch();

        $match->expireAt = Carbon::now()->addMinutes(-10);
        $match->save();

        event(new DeleteMatch($match->id));

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

        if (isset($request->invitationLink) && !preg_match('/https?:\/\/discord\.gg\/[a-z0-9]{3,}/i', $request->invitationLink)) {
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

        $this->buildNotification($match);

        return response()->json(['match' => $userMatch]);
    }

    public function getMatch(Request $request, $id)
    {
        $match = Match::where('expireAt', '>', Carbon::now())->with('user')->find($id);

        if (is_null($match)) {
            return redirect()->route('home')->with('match', '0');
        }

        return redirect()->route('home')->with('match', $match);
    }

    private function userMatch()
    {
        return User::find(Auth::id())->matches()->with('user')->where('expireAt', '>', Carbon::now())->firstOrFail();
    }

    private function buildNotification($match)
    {
        $howMany = $match->howMuch;

        $howMany = $howMany . str_repeat(' :person_frowning:', $howMany);

        $games = [
            'qp' => 'Quick Play',
            'comp' => 'Competitive',
            'custom' => 'Custom games',
            'brawl' => 'Brawl',
        ];

        $fields = [
            [
                'name' => 'Region',
                'value' => ':flag_' . $match->region . ': ' . strtoupper($match->region),
                'inline' => true,
            ],
            [
                'name' => 'Type',
                'value' => $games[$match->type],
                'inline' => true,
            ],
            [
                'name' => 'Languages',
                'value' => strtoupper(implode(' ', $match->languages)),
                'inline' => true,
            ],
            [
                'name' => 'How Many',
                'value' => $howMany,
                'inline' => true,
            ],
        ];

        if ($match->type == 'comp') {
            $fields[] = [
                'name' => 'Min Rank',
                'value' => $match->minRank,
                'inline' => true,
            ];
            $fields[] = [
                'name' => 'Max Rank',
                'value' => $match->maxRank,
                'inline' => true,
            ];
        } else {
            $fields[] = [
                'name' => 'Description',
                'value' => $match->description,
                'inline' => true,
            ];
        }

        if ($match->invitationLink) {
            $fields[] = [
                'name' => 'Invitation',
                'value' => $match->invitationLink,
                'inline' => true,
            ];
        }

        if (Auth::user()->discord_id) {
            $fields[] = [
                'name' => 'Discord Tag',
                'value' => '<@' . Auth::user()->discord_id . '>',
                'inline' => true,
            ];
        }

        $message = $this->getDiscordClient()->channel->createMessage([
            'channel.id' => intval(env('DISCORD_CHANNELID')),
            'content' => ':white_check_mark: Available',
            'embed' => [
                'title' => ':book: More details',
                'url' => route('getMatch', $match->id),
                'color' => 14290439,

                'fields' => $fields,
                'author' => [
                    'name' => 'Want to create your own lobby? Click here!',
                    'url' => route('home'),
                    'icon_url' => 'https://overwatchlounge.herokuapp.com/images/logo.png',
                ],
            ],
        ]);

        $match->message_id = $message['id'];
        $match->save();
    }

    private function getDiscordClient()
    {
        return new DiscordClient(['token' => env('DISCORD_TOKEN')]);
    }
}
