<?php

namespace Fabio\ErrorManager\Providers;

use Error;
use Fabio\ErrorManager\Contracts\ErrorDispatcherInterface;
use Fabio\ErrorManager\Exception\ErrorDispatcher;
use Fabio\UltraSecureUpload\ConfigManager;
use Illuminate\Support\ServiceProvider;

class ErrorManagerServiceProvider extends ServiceProvider
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