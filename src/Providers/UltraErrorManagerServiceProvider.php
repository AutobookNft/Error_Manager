<?php

namespace Fabio\UltraErrorManager\Providers;

use Error;
use Fabio\UltraErrorManager\Contracts\ErrorDispatcherInterface;
use Fabio\UltraErrorManager\Exception\ErrorDispatcher;
use Fabio\PerfectConfigManager\ConfigManager;
use Illuminate\Support\ServiceProvider;

class UltraErrorManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ErrorDispatcherInterface::class, ErrorDispatcher::class);

        // Registrazione del ConfigManager come singleton
        $this->app->singleton(ConfigManager::class, function ($app) {
            return new ConfigManager();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/error_messages.php' => config_path('error_messages.php'),
        ]);
    }
}