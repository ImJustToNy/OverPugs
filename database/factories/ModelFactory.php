<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
 */

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(OverPugs\User::class, function (Faker\Generator $faker) {
    return [
        'tag' => $faker->firstName . '#' . $faker->numberBetween(1000, 9999),
        'prefered_region' => 'us',
        'us_profile' => json_encode([
            'rank' => $faker->numberBetween(1000, 9999),
            'avatarUrl' => 'https://blzgdapipro-a.akamaihd.net/game/unlocks/0x0250000000000742.png',
        ]),
        'discord_nickname' => $faker->lastName,
        'discord_id' => 143702416100032513,
        'discord_avatar_url' => 'https://blzgdapipro-a.akamaihd.net/game/unlocks/0x0250000000000742.png',
    ];
});

$factory->define(OverPugs\Match::class, function (Faker\Generator $faker) {
    return [
        'user_id' => function () {
            return factory(OverPugs\User::class)->create()->id;
        },
        'type' => 'comp',
        'region' => 'eu',
        'languages' => 'pl,de',
        'howMuch' => 1,
        'minRank' => 2000,
        'maxRank' => 2500,
    ];
});

$factory->state(OverPugs\Match::class, 'expired', function ($faker) {
    return [
        'expireAt' => Carbon\Carbon::now()->addMinutes(-10),
    ];
});
