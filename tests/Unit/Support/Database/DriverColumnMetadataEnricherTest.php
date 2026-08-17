<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Support\Database\DriverColumnMetadataEnricher;
use SimbaJirira\SchemaContract\Support\DatabaseColumnNormalizer;
use SimbaJirira\SchemaContract\Support\RawColumnMetadata;
use SimbaJirira\SchemaContract\Support\SchemaColumnMetadataFactory;

it('enriches mysql enum and decimal metadata from column_type values', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'status',
        'type' => "enum('active','inactive')",
        'type_name' => 'enum',
        'nullable' => true,
        'default' => 'active',
    ], 'mysql');

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Enum)
        ->and($column->originalDriverType)->toBe("enum('active','inactive')");
});

it('enriches postgresql integer aliases from type_name metadata', function () {
    $metadata = (new DriverColumnMetadataEnricher)->enrich('pgsql', [
        'name' => 'count',
        'type' => 'integer',
        'type_name' => 'int4',
        'nullable' => false,
    ], new RawColumnMetadata(
        name: 'count',
        driverType: 'integer',
    ));

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Integer);
});

it('enriches sqlite decimal precision when present in driver metadata', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'bonus',
        'type' => 'decimal(10,2)',
        'type_name' => 'decimal',
        'nullable' => true,
        'default' => null,
    ], 'sqlite');

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Decimal)
        ->and($column->precision)->toBe(10)
        ->and($column->scale)->toBe(2);
});

it('preserves unknown metadata when driver arrays are incomplete', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'legacy',
        'type' => '',
        'type_name' => '',
        'nullable' => true,
    ], 'mysql');

    $column = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($column->type)->toBe(DatabaseType::Unknown)
        ->and($column->originalDriverType)->toBe('unknown');
});

it('maps mysql tinyint one columns to boolean', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'active',
        'type' => 'tinyint(1)',
        'type_name' => 'tinyint',
        'nullable' => false,
        'default' => '1',
    ], 'mysql');

    expect((new DatabaseColumnNormalizer)->normalize($metadata)->type)->toBe(DatabaseType::Boolean);
});

it('maps postgresql uuid and jsonb types from driver metadata fixtures', function (array $column, DatabaseType $expected) {
    $metadata = (new SchemaColumnMetadataFactory)->make($column, 'pgsql');
    $normalized = (new DatabaseColumnNormalizer)->normalize($metadata);

    expect($normalized->type)->toBe($expected);
})->with([
    'uuid' => [[
        'name' => 'external_id',
        'type' => 'uuid',
        'type_name' => 'uuid',
        'nullable' => true,
        'default' => null,
    ], DatabaseType::Uuid],
    'jsonb' => [[
        'name' => 'payload',
        'type' => 'jsonb',
        'type_name' => 'jsonb',
        'nullable' => true,
        'default' => null,
    ], DatabaseType::Json],
    'timestamptz' => [[
        'name' => 'archived_at',
        'type' => 'timestamp with time zone',
        'type_name' => 'timestamptz',
        'nullable' => true,
        'default' => null,
    ], DatabaseType::Timestamp],
]);
