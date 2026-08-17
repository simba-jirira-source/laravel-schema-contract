<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\SchemaContractServiceProvider;

it('boots the package in the testbench application', function () {
    expect(app()->bound('config'))->toBeTrue();
});

it('loads the schema contract service provider', function () {
    expect(collect(app()->getLoadedProviders())->keys())
        ->toContain(SchemaContractServiceProvider::class);
});

it('merges default package configuration', function () {
    expect(config('schema-contract.model_paths'))
        ->toBeArray()
        ->not->toBeEmpty();

    expect(config('schema-contract.ignore_models'))->toBeArray();
    expect(config('schema-contract.ignore_columns'))->toBeArray();
});

it('allows the host application to override configuration', function () {
    $this->app['config']->set('schema-contract.model_paths', ['/custom/models']);
    $this->app['config']->set('schema-contract.ignore_models', ['App\\Models\\Legacy']);
    $this->app['config']->set('schema-contract.ignore_columns', ['users' => ['password']]);

    expect(config('schema-contract.model_paths'))->toBe(['/custom/models']);
    expect(config('schema-contract.ignore_models'))->toBe(['App\\Models\\Legacy']);
    expect(config('schema-contract.ignore_columns'))->toBe(['users' => ['password']]);
});

it('publishes the configuration file', function () {
    $publishedConfig = config_path('schema-contract.php');

    if (file_exists($publishedConfig)) {
        unlink($publishedConfig);
    }

    $this->artisan('vendor:publish', ['--tag' => 'schema-contract-config'])
        ->assertSuccessful();

    expect($publishedConfig)->toBeFile();

    if (file_exists($publishedConfig)) {
        unlink($publishedConfig);
    }
});
