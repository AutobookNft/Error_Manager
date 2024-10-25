<?php

namespace Fabio\UltraLogManager\Providers;

use Fabio\UltraLogManager\UltraLogManager;
use Illuminate\Support\ServiceProvider;

class UltraLogManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
        // Register the UltraLogManager class
        $this->app->singleton('ultralogmanager', function ($app) {
            return new UltraLogManager();
        });
        
        $this->mergeConfigFrom(__DIR__ . '/../Config/logging.php', 'logging');


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Make the logging configuration publishable, so the users can override it if needed
        $this->publishes([
            __DIR__ . '/../Config/logging.php' => config_path('logging.php'),
        ]);
    }
}
