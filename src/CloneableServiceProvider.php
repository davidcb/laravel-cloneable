<?php

namespace Davidcb\LaravelCloneable;

use Davidcb\LaravelCloneable\Cloner;
use Illuminate\Support\ServiceProvider;
use Davidcb\LaravelCloneable\Adapters\LaravelMediaLibrary;

class CloneableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $this->mergeConfigFrom(__DIR__ . '/../config', 'laravel-cloneable');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Instantiate main Cloner instance
        $this->app->singleton('cloner', function ($app) {
            return new Cloner(
                $app['cloner.attachment-adapter'],
                $app['events']
            );
        });

        $this->app->singleton('cloner.attachment-adapter', function ($app) {
            return new LaravelMediaLibrary();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'cloner',
            'cloner.attachment-adapter',
        ];
    }
}
