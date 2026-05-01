<?php

namespace Aliziodev\Biteship;

use Aliziodev\Biteship\Console\Commands\InstallCommand;
use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\Http\BiteshipClient;
use Aliziodev\Biteship\Http\MockBiteshipClient;
use Illuminate\Support\ServiceProvider;

class BiteshipServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/biteship.php', 'biteship');

        $this->app->singleton(BiteshipClientInterface::class, function ($app) {
            // Use mock client if mock mode is enabled
            if (config('biteship.mock_mode.enabled', false)) {
                return new MockBiteshipClient;
            }

            return new BiteshipClient(
                $app->make('Illuminate\Http\Client\Factory'),
                config('biteship.api_key') ?? '',
            );
        });

        $this->app->singleton(Biteship::class, function ($app) {
            return new Biteship($app->make(BiteshipClientInterface::class));
        });

        $this->app->alias(Biteship::class, 'biteship');
    }

    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerViews();
    }

    private function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/biteship.php' => config_path('biteship.php'),
        ], 'biteship-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'biteship-migrations');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/biteship'),
        ], 'biteship-views');
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    private function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'biteship');
    }
}
