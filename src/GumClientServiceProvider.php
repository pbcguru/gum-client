<?php

namespace Pbc\GumClient;

use Illuminate\Support\ServiceProvider;

class GumClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gum.php', 'gum');

        $this->app->singleton(GumClient::class, function () {
            return new GumClient();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/gum.php' => config_path('gum.php'),
        ], 'gum-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
