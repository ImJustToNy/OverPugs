<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use OverPugs\Events\DeleteMatch;
use OverPugs\Events\NewMatch;
use OverPugs\Events\UpdateExpire;
use OverPugs\Jobs\BuildDiscordNotification;
use OverPugs\User;
use Tests\TestCase;

class AddMatchTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    /**
     * Generate user and setup fake queue and event bus.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Queue::fake();
        Event::fake();

        $this->user = factory(User::class)->create();
    }

    /**
     * Adding correct competitive match,
     * refreshing it,
     * trying to check it out
     * deleting it
     * and editing discord message.
     *
     * @return void
     */
    public function testCorrectMatch()
    {
        $match = $this->actingAs($this->user)
            ->json('POST', '/api/match/add', [
                'type'           => 'comp',
                'region'         => 'us',
                'languages'      => ['pl', 'de'],
                'howMuch'        => 3,
                'minRank'        => $this->user->rank - 100,
                'maxRank'        => $this->user->rank + 100,
                'invitationLink' => 'https://discord.gg/overwatch',
            ])
            ->assertStatus(200)
            ->assertJson([
                'match' => [
                    'type' => 'comp',
                ],
            ])
            ->decodeResponseJson();

        Queue::assertPushed(BuildDiscordNotification::class);
        Event::assertDispatched(NewMatch::class, function ($event) use ($match) {
            return $match['match']['id'] == $event->match['id'];
        });

        $refreshedMatch = $this->actingAs($this->user)
            ->json('POST', '/api/match/refresh')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'too fast'
            ])
            ->decodeResponseJson();

        Event::assertNotDispatched(UpdateExpire::class);

        $this->json('GET', '/match/'.$match['match']['id'])
            ->assertRedirect('/')
            ->assertSessionHas('match.id', $match['match']['id']);

        $this->actingAs($this->user)
            ->json('POST', '/api/match/delete')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);

        Event::assertDispatched(DeleteMatch::class, function ($event) use ($refreshedMatch) {
            return $refreshedMatch['match']['id'] == $event->id;
        });
    }

    /**
     * Trying to add quick play match without
     * description (which is required) and expecting 422 (validation error).
     *
     * @return void
     */
    public function testIncorrectMatch()
    {
        $this->actingAs($this->user)
            ->json('POST', '/api/match/add', [
                'type'      => 'qp',
                'region'    => 'us',
                'languages' => ['pl', 'de'],
                'howMuch'   => 3,
            ])
            ->assertStatus(422);

        Queue::assertNotPushed(BuildDiscordNotification::class);
    }
}
