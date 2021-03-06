<?php

namespace OverPugs\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'OverPugs\Events\SomeEvent' => [
            'OverPugs\Listeners\EventListener',
        ],

        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\Battlenet\BattlenetExtendSocialite@handle',
            'SocialiteProviders\Discord\DiscordExtendSocialite@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
