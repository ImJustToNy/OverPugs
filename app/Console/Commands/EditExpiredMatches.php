<?php

namespace OverPugs\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use OverPugs\Match;

class EditExpiredMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update expired messages from discord server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $matches = Match::where('expireAt', '<', Carbon::now())->where('message_deleted', false)->get();

        foreach ($matches as $match) {
            if (!App::environment('testing')) {
                resolve('RestCord\DiscordClient')->channel->editMessage([
                    'channel.id' => intval(env('DISCORD_CHANNELID')),
                    'message.id' => intval($match->message_id),
                    'content'    => ':no_entry_sign: Expired',
                ]);
            }

            $match->message_deleted = true;
            $match->save();
        }
    }
}
