<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use OverPugs\Match;
use Tests\TestCase;

class ExpireCommandTest extends TestCase
{
    /**
     * Creates new expired match and tires if discord message would be editted successfuly
     *
     * @return void
     */
    public function testExpiredCommand()
    {
        $match = factory(Match::class)->states('expired')->create();

        Artisan::call('schedule:run');

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'message_deleted' => true,
        ]);
    }
}
