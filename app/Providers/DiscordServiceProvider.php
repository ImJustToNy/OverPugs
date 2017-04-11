<?php

namespace OverPugs\Providers;

use Illuminate\Support\ServiceProvider;
use RestCord\DiscordClient;

class DiscordServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DiscordClient::class, function ($app) {
            return new DiscordClient(['token' => env('DISCORD_TOKEN')]);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [DiscordClient::class];
    }
}
