<?php

declare(strict_types=1);

namespace LaravelSchemaContract\LaravelSchemaContract;

use Illuminate\Support\ServiceProvider;
use LaravelSchemaContract\LaravelSchemaContract\Console\Commands\LaravelSchemaContractCommand;

class LaravelSchemaContractServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-schema-contract.php', 'laravel-schema-contract');

        $this->app->singleton(LaravelSchemaContract::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/laravel-schema-contract.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-schema-contract');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-schema-contract');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-schema-contract.php' => config_path('laravel-schema-contract.php'),
        ], ['laravel-schema-contract', 'laravel-schema-contract-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-schema-contract'),
        ], ['laravel-schema-contract', 'laravel-schema-contract-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/laravel-schema-contract'),
        ], ['laravel-schema-contract', 'laravel-schema-contract-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-schema-contract'),
        ], ['laravel-schema-contract', 'laravel-schema-contract-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['laravel-schema-contract', 'laravel-schema-contract-migrations']);

        $this->commands([
            LaravelSchemaContractCommand::class,
        ]);
    }
}
