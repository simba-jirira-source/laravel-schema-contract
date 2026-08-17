<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;

it('defines every normalized cast type required for v0.1', function (CastType $type, string $value) {
    expect($type->value)->toBe($value);
})->with([
    [CastType::Boolean, 'boolean'],
    [CastType::Integer, 'integer'],
    [CastType::Float, 'float'],
    [CastType::Double, 'double'],
    [CastType::Decimal, 'decimal'],
    [CastType::String, 'string'],
    [CastType::Array, 'array'],
    [CastType::Object, 'object'],
    [CastType::Collection, 'collection'],
    [CastType::Date, 'date'],
    [CastType::DateTime, 'datetime'],
    [CastType::Timestamp, 'timestamp'],
    [CastType::Enum, 'enum'],
    [CastType::Custom, 'custom'],
    [CastType::Unknown, 'unknown'],
]);

it('can be restored from its backed value', function () {
    expect(CastType::from('decimal'))->toBe(CastType::Decimal);
    expect(CastType::tryFrom('custom'))->toBe(CastType::Custom);
    expect(CastType::tryFrom('immutable_datetime'))->toBeNull();
});
