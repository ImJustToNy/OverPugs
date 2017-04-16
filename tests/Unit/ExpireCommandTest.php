<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use OverPugs\Match;
use Tests\TestCase;

class ExpireCommandTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $match = factory(Match::class)->states('expired')->create();

        Artisan::call('schedule:run');

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'message_deleted' => true,
        ]);
    }
}
