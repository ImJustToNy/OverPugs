<?php

namespace OverSearch\Providers;

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
        'OverSearch\Events\SomeEvent' => [
            'OverSearch\Listeners\EventListener',
        ],

        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\Battlenet\BattlenetExtendSocialite@handle',
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
