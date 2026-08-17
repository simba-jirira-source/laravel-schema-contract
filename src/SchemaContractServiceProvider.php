<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract;

use Illuminate\Support\ServiceProvider;

class SchemaContractServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/schema-contract.php', 'schema-contract');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/schema-contract.php' => config_path('schema-contract.php'),
        ], 'schema-contract-config');
    }
}
