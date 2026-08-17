<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Inspectors\EloquentModelInspector;
use SimbaJirira\SchemaContract\Inspectors\EloquentSchemaInspector;
use SimbaJirira\SchemaContract\Support\DatabaseColumnNormalizer;
use SimbaJirira\SchemaContract\Support\SchemaColumnMetadataFactory;
use SimbaJirira\SchemaContract\Tests\Fixtures\Database\DriverCompatibilityProfile;

beforeEach(function (): void {
    skipUnlessDatabaseDriver('mysql');

    $this->connection = configureDriverTestingConnection('mysql');
    $this->modelInspector = new EloquentModelInspector;
    $this->schemaInspector = new EloquentSchemaInspector;

    createDriverCompatibilityTables($this->connection, 'mysql');
});

it('normalizes representative mysql column types from live schema metadata', function () {
    $model = $this->modelInspector->inspect(DriverCompatibilityProfile::class);
    $table = $this->schemaInspector->inspect($model);

    expect(driverCompatibilityColumn($table, 'active')->type)->toBe(DatabaseType::Boolean)
        ->and(driverCompatibilityColumn($table, 'small_count')->type)->toBe(DatabaseType::SmallInteger)
        ->and(driverCompatibilityColumn($table, 'count')->type)->toBe(DatabaseType::Integer)
        ->and(driverCompatibilityColumn($table, 'big_count')->type)->toBe(DatabaseType::BigInteger)
        ->and(driverCompatibilityColumn($table, 'amount')->type)->toBe(DatabaseType::Decimal)
        ->and(driverCompatibilityColumn($table, 'amount')->precision)->toBe(10)
        ->and(driverCompatibilityColumn($table, 'amount')->scale)->toBe(2)
        ->and(driverCompatibilityColumn($table, 'payload')->type)->toBe(DatabaseType::Json)
        ->and(driverCompatibilityColumn($table, 'external_id')->type)->toBe(DatabaseType::String)
        ->and(driverCompatibilityColumn($table, 'status')->type)->toBe(DatabaseType::Enum)
        ->and(driverCompatibilityColumn($table, 'archived_at')->type)->toBe(DatabaseType::Timestamp);
})->group('mysql');

it('retains mysql enum metadata on normalized columns', function () {
    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(DriverCompatibilityProfile::class),
    );

    $status = driverCompatibilityColumn($table, 'status');

    expect($status->type)->toBe(DatabaseType::Enum)
        ->and($status->originalDriverType)->toContain('enum');
})->group('mysql');

it('normalizes mysql schema column arrays through the metadata factory', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'amount',
        'type' => 'decimal(12,4) unsigned',
        'type_name' => 'decimal',
        'nullable' => true,
        'default' => null,
    ], 'mysql');

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Decimal)
        ->and($column->precision)->toBe(12)
        ->and($column->scale)->toBe(4);
})->group('mysql');
