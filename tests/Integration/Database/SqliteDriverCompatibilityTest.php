<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Inspectors\EloquentModelInspector;
use SimbaJirira\SchemaContract\Inspectors\EloquentSchemaInspector;
use SimbaJirira\SchemaContract\Tests\Fixtures\Database\DriverCompatibilityProfile;

beforeEach(function (): void {
    $this->modelInspector = new EloquentModelInspector;
    $this->schemaInspector = new EloquentSchemaInspector;

    config(['database.connections.driver_testing' => config('database.connections.testing')]);

    createDriverCompatibilityTables('driver_testing', 'sqlite');
});

it('normalizes representative sqlite column types from live schema metadata', function () {
    $model = $this->modelInspector->inspect(DriverCompatibilityProfile::class);
    $table = $this->schemaInspector->inspect($model);

    expect(driverCompatibilityColumn($table, 'active')->type)->toBe(DatabaseType::Boolean)
        ->and(driverCompatibilityColumn($table, 'count')->type)->toBe(DatabaseType::Integer)
        ->and(driverCompatibilityColumn($table, 'amount')->type)->toBe(DatabaseType::Decimal)
        ->and(driverCompatibilityColumn($table, 'ratio')->type)->toBe(DatabaseType::Float)
        ->and(driverCompatibilityColumn($table, 'precise_ratio')->type)->toBe(DatabaseType::Double)
        ->and(driverCompatibilityColumn($table, 'code')->type)->toBe(DatabaseType::String)
        ->and(driverCompatibilityColumn($table, 'bio')->type)->toBe(DatabaseType::Text)
        ->and(driverCompatibilityColumn($table, 'payload')->type)->toBe(DatabaseType::Json)
        ->and(driverCompatibilityColumn($table, 'starts_on')->type)->toBe(DatabaseType::Date)
        ->and(driverCompatibilityColumn($table, 'published_at')->type)->toBe(DatabaseType::DateTime)
        ->and(driverCompatibilityColumn($table, 'archived_at')->type)->toBe(DatabaseType::DateTime);
});

it('documents sqlite integer size aliasing as a single integer type', function () {
    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(DriverCompatibilityProfile::class),
    );

    expect(driverCompatibilityColumn($table, 'small_count')->type)->toBe(DatabaseType::Integer)
        ->and(driverCompatibilityColumn($table, 'big_count')->type)->toBe(DatabaseType::Integer);
});

it('preserves sqlite decimal precision when the driver exposes it', function () {
    Schema::connection('driver_testing')->getConnection()->statement(
        'ALTER TABLE driver_compatibility_profiles ADD COLUMN bonus DECIMAL(10,2) NULL',
    );

    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(DriverCompatibilityProfile::class),
    );

    $bonus = driverCompatibilityColumn($table, 'bonus');

    expect($bonus->type)->toBe(DatabaseType::Decimal)
        ->and($bonus->precision)->toBe(10)
        ->and($bonus->scale)->toBe(2);
});

it('maps sqlite uuid columns to string metadata rather than native uuid', function () {
    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(DriverCompatibilityProfile::class),
    );

    expect(driverCompatibilityColumn($table, 'external_id')->type)->toBe(DatabaseType::String);
});

it('maps unsupported sqlite driver metadata to unknown without crashing', function () {
    Schema::connection('driver_testing')->getConnection()->statement(
        'ALTER TABLE driver_compatibility_profiles ADD COLUMN legacy_flag WEIRD_TYPE NULL',
    );

    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(DriverCompatibilityProfile::class),
    );

    expect(driverCompatibilityColumn($table, 'legacy_flag')->type)->toBe(DatabaseType::Unknown);
});
