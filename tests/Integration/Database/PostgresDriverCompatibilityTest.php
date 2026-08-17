<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Inspectors\EloquentModelInspector;
use SimbaJirira\SchemaContract\Inspectors\EloquentSchemaInspector;
use SimbaJirira\SchemaContract\Support\DatabaseColumnNormalizer;
use SimbaJirira\SchemaContract\Support\SchemaColumnMetadataFactory;
use SimbaJirira\SchemaContract\Tests\Fixtures\Database\DriverCompatibilityProfile;

beforeEach(function (): void {
    skipUnlessDatabaseDriver('pgsql');

    $this->connection = configureDriverTestingConnection('pgsql');
    $this->modelInspector = new EloquentModelInspector;
    $this->schemaInspector = new EloquentSchemaInspector;

    createDriverCompatibilityTables($this->connection, 'pgsql');
});

it('normalizes representative postgresql column types from live schema metadata', function () {
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
        ->and(driverCompatibilityColumn($table, 'external_id')->type)->toBe(DatabaseType::Uuid)
        ->and(driverCompatibilityColumn($table, 'archived_at')->type)->toBe(DatabaseType::Timestamp);
})->group('pgsql');

it('normalizes postgresql schema column arrays through the metadata factory', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'payload',
        'type' => 'jsonb',
        'type_name' => 'jsonb',
        'nullable' => true,
        'default' => null,
    ], 'pgsql');

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Json)
        ->and($column->originalDriverType)->toBe('jsonb');
})->group('pgsql');

it('maps postgresql unknown user-defined types to unknown without crashing', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'geo',
        'type' => 'geometry',
        'type_name' => 'geometry',
        'nullable' => true,
        'default' => null,
    ], 'pgsql');

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Unknown);
})->group('pgsql');
