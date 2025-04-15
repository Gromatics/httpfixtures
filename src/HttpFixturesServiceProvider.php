<?php

namespace Gromatics\HttpFixtures;

use Gromatics\HttpFixtures\Console\MakeHttpFixture;
use Illuminate\Support\ServiceProvider;

class HttpFixturesServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind services into the container here
    }

    public function boot()
    {
        // Publish config, load routes/views/etc. here

        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeHttpFixture::class,
            ]);
        }
    }
}
