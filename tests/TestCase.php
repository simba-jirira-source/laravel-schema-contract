<?php

declare(strict_types=1);

namespace LaravelSchemaContract\LaravelSchemaContract\Tests;

use LaravelSchemaContract\LaravelSchemaContract\LaravelSchemaContractServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelSchemaContractServiceProvider::class,
        ];
    }
}
