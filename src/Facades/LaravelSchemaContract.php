<?php

declare(strict_types=1);

namespace LaravelSchemaContract\LaravelSchemaContract\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelSchemaContract\LaravelSchemaContract\LaravelSchemaContract
 */
class LaravelSchemaContract extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelSchemaContract\LaravelSchemaContract\LaravelSchemaContract::class;
    }
}
