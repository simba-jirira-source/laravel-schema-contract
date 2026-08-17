<?php

declare(strict_types=1);

use LaravelSchemaContract\LaravelSchemaContract\LaravelSchemaContract;

it('resolves the singleton', function () {
    expect(app(LaravelSchemaContract::class))->toBeInstanceOf(LaravelSchemaContract::class);
});

it('returns the same instance from the container', function () {
    expect(app(LaravelSchemaContract::class))->toBe(app(LaravelSchemaContract::class));
});

it('merges the package config', function () {
    expect(config('laravel-schema-contract.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('laravel-schema-contract::messages.placeholder'))->toBe('LaravelSchemaContract placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('laravel-schema-contract::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('laravel-schema-contract:placeholder')
        ->expectsOutputToContain('LaravelSchemaContract placeholder command executed.')
        ->assertSuccessful();
});
