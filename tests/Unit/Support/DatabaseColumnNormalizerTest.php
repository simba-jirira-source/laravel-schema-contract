<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Support\DatabaseColumnNormalizer;
use SimbaJirira\SchemaContract\Support\RawColumnMetadata;

beforeEach(function (): void {
    $this->normalizer = new DatabaseColumnNormalizer;
});

it('normalizes common integer and string types', function (string $driverType, DatabaseType $expected) {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'column',
        driverType: $driverType,
    ));

    expect($column->type)->toBe($expected);
    expect($column->originalDriverType)->toBe($driverType);
})->with([
    ['int', DatabaseType::Integer],
    ['integer', DatabaseType::Integer],
    ['bigint', DatabaseType::BigInteger],
    ['smallint', DatabaseType::SmallInteger],
    ['varchar(255)', DatabaseType::String],
    ['text', DatabaseType::Text],
    ['boolean', DatabaseType::Boolean],
    ['bool', DatabaseType::Boolean],
    ['date', DatabaseType::Date],
    ['datetime', DatabaseType::DateTime],
    ['timestamp', DatabaseType::Timestamp],
    ['json', DatabaseType::Json],
]);

it('normalizes driver-specific aliases across sqlite mysql and postgresql', function (string $driverType, DatabaseType $expected) {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'column',
        driverType: $driverType,
    ));

    expect($column->type)->toBe($expected);
})->with([
    ['INTEGER', DatabaseType::Integer],
    ['BIGINT', DatabaseType::BigInteger],
    ['TINYINT', DatabaseType::SmallInteger],
    ['REAL', DatabaseType::Float],
    ['DOUBLE', DatabaseType::Double],
    ['JSONB', DatabaseType::Json],
    ['UUID', DatabaseType::Uuid],
    ['BYTEA', DatabaseType::Binary],
    ['CHARACTER VARYING(100)', DatabaseType::String],
    ['DOUBLE PRECISION', DatabaseType::Double],
    ['TIMESTAMP WITH TIME ZONE', DatabaseType::Timestamp],
    ['ENUM(\'active\',\'inactive\')', DatabaseType::Enum],
    ['MEDIUMINT', DatabaseType::Integer],
    ['LONGTEXT', DatabaseType::Text],
    ['VARBINARY(16)', DatabaseType::Binary],
    ['citext', DatabaseType::String],
    ['year', DatabaseType::Integer],
]);

it('maps mysql tinyint(1) to boolean', function () {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'active',
        driverType: 'tinyint(1)',
    ));

    expect($column->type)->toBe(DatabaseType::Boolean);
});

it('preserves decimal precision and scale from driver type strings', function () {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'credit_limit',
        driverType: 'decimal(10,2)',
        nullable: true,
    ));

    expect($column->type)->toBe(DatabaseType::Decimal);
    expect($column->precision)->toBe(10);
    expect($column->scale)->toBe(2);
    expect($column->nullable)->toBeTrue();
});

it('prefers explicitly provided metadata over parsed driver type values', function () {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'amount',
        driverType: 'decimal(8,2)',
        precision: 12,
        scale: 4,
        length: 50,
    ));

    expect($column->precision)->toBe(12);
    expect($column->scale)->toBe(4);
    expect($column->length)->toBe(50);
});

it('preserves nullable and default metadata from the schema api', function () {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'email',
        driverType: 'varchar(255)',
        nullable: true,
        default: 'guest@example.com',
        length: 191,
    ));

    expect($column->nullable)->toBeTrue();
    expect($column->default)->toBe('guest@example.com');
    expect($column->length)->toBe(191);
});

it('parses string length when explicit length is not provided', function () {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'code',
        driverType: 'varchar(32)',
    ));

    expect($column->length)->toBe(32);
});

it('maps unknown and custom driver types to unknown without throwing', function (string $driverType) {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'metadata',
        driverType: $driverType,
    ));

    expect($column->type)->toBe(DatabaseType::Unknown);
    expect($column->originalDriverType)->toBe($driverType);
})->with([
    'geography',
    'geometry',
    'point',
    'polygon',
    'set(\'a\',\'b\')',
    'custom_type',
    '',
]);

it('strips unsigned modifiers before mapping integer types', function () {
    $column = $this->normalizer->normalize(new RawColumnMetadata(
        name: 'count',
        driverType: 'int unsigned',
    ));

    expect($column->type)->toBe(DatabaseType::Integer);
});
