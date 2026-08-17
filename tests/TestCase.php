<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SimbaJirira\SchemaContract\SchemaContractServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SchemaContractServiceProvider::class,
        ];
    }
}
