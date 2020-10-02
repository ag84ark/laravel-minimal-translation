<?php

namespace Ag84ark\LaravelMinimalTranslation;

use Ag84ark\LaravelMinimalTranslation\Console\Commands\LMTCommand;
use Illuminate\Support\ServiceProvider;

class LaravelMinimalTranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-minimal-translation');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-minimal-translation');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-minimal-translation.php'),
            ], 'lmt-config');

            // Publishing the views.
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'lmt');
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-minimal-translation'),
            ], 'views');

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/laravel-minimal-translation'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-minimal-translation'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                 LMTCommand::class
             ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-minimal-translation');

        // Register the main class to use with the facade
        $this->app->singleton('laravel-minimal-translation', function () {
            return new LaravelMinimalTranslation;
        });
    }
}
