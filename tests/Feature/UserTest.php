<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use OverPugs\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testChangingRegion()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->json('POST', '/api/changeRegion', [
                'region' => 'eu',
            ])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'prefered_region' => 'eu',
        ]);
    }
}
