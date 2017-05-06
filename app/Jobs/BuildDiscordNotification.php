<?php

namespace OverPugs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use OverPugs\Match;

class BuildDiscordNotification implements ShouldQueue
{
    private $match;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance with $match variable.
     *
     * @return void
     */
    public function __construct(Match $match)
    {
        $this->match = $match;
    }

    /**
     * Build up a discord notification and send it.
     *
     * @return void
     */
    public function handle()
    {
        $howMany = $this->match->howMuch;

        $howMany = $howMany.str_repeat(' :person_frowning:', $howMany);

        $games = [
            'qp'     => 'Quick Play',
            'comp'   => 'Competitive',
            'custom' => 'Custom games',
            'brawl'  => 'Brawl',
        ];

        $fields = [
            [
                'name'   => 'Region',
                'value'  => ':flag_'.$this->match->region.': '.strtoupper($this->match->region),
                'inline' => true,
            ],
            [
                'name'   => 'Type',
                'value'  => $games[$this->match->type],
                'inline' => true,
            ],
            [
                'name'   => 'Languages',
                'value'  => strtoupper(implode(' ', $this->match->languages)),
                'inline' => true,
            ],
            [
                'name'   => 'How Many',
                'value'  => $howMany,
                'inline' => true,
            ],
        ];

        if ($this->match->type == 'comp') {
            $fields[] = [
                'name'   => 'Min Rank',
                'value'  => $this->match->minRank,
                'inline' => true,
            ];
            $fields[] = [
                'name'   => 'Max Rank',
                'value'  => $this->match->maxRank,
                'inline' => true,
            ];
        } else {
            $fields[] = [
                'name'   => 'Description',
                'value'  => $this->match->description,
                'inline' => true,
            ];
        }

        if ($this->match->invitationLink) {
            $fields[] = [
                'name'   => 'Invitation',
                'value'  => $this->match->invitationLink,
                'inline' => true,
            ];
        }

        if (Auth::user()->discord_id) {
            $fields[] = [
                'name'   => 'Discord Tag',
                'value'  => '<@'.Auth::user()->discord_id.'>',
                'inline' => true,
            ];
        }

        $message = resolve('RestCord\DiscordClient')->channel->createMessage([
            'channel.id' => intval(env('DISCORD_CHANNELID')),
            'content'    => ':white_check_mark: Available',
            'embed'      => [
                'title' => ':book: More details',
                'url'   => route('getMatch', $this->match->id),
                'color' => 14290439,

                'fields' => $fields,
                'author' => [
                    'name'     => 'Want to create your own lobby? Click here!',
                    'url'      => route('home'),
                    'icon_url' => asset('images/logo.png'),
                ],
            ],
        ]);

        $this->match->message_id = $message['id'];
        $this->match->save();
    }
}
