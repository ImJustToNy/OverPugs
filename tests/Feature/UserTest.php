<?php

namespace Tests\Feature;

use OverPugs\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Test changing regions
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
