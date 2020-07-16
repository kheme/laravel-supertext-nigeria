<?php

namespace Kheme\SuperTextNg;

use Illuminate\Support\ServiceProvider;
use Kheme\SuperTextNg\Services\SMS;

class SuperTextNgServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/supertextng.php' =>  config_path('supertextng.php'),
         ], 'config');

        $this->app->bind('supertextng', function () {
            return new SMS();
        });
    }
}
