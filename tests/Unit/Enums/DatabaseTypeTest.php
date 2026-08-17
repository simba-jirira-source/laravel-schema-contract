<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\DatabaseType;

it('defines every normalized database type required for v0.1', function (DatabaseType $type, string $value) {
    expect($type->value)->toBe($value);
})->with([
    [DatabaseType::Boolean, 'boolean'],
    [DatabaseType::Integer, 'integer'],
    [DatabaseType::BigInteger, 'big_integer'],
    [DatabaseType::SmallInteger, 'small_integer'],
    [DatabaseType::Decimal, 'decimal'],
    [DatabaseType::Float, 'float'],
    [DatabaseType::Double, 'double'],
    [DatabaseType::String, 'string'],
    [DatabaseType::Text, 'text'],
    [DatabaseType::Date, 'date'],
    [DatabaseType::DateTime, 'datetime'],
    [DatabaseType::Timestamp, 'timestamp'],
    [DatabaseType::Json, 'json'],
    [DatabaseType::Uuid, 'uuid'],
    [DatabaseType::Enum, 'enum'],
    [DatabaseType::Binary, 'binary'],
    [DatabaseType::Unknown, 'unknown'],
]);

it('can be restored from its backed value', function () {
    expect(DatabaseType::from('decimal'))->toBe(DatabaseType::Decimal);
    expect(DatabaseType::tryFrom('unknown'))->toBe(DatabaseType::Unknown);
    expect(DatabaseType::tryFrom('geography'))->toBeNull();
});
