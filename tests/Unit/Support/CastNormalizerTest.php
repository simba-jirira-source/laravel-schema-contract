<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Support\CastNormalizer;
use SimbaJirira\SchemaContract\Tests\Fixtures\Casts\PreferencesCast;
use SimbaJirira\SchemaContract\Tests\Fixtures\Enums\AccountStatus;

beforeEach(function (): void {
    $this->normalizer = new CastNormalizer;
});

it('normalizes built-in scalar and json casts', function (string $expression, CastType $expected) {
    $cast = $this->normalizer->normalize('column', $expression);

    expect($cast->type)->toBe($expected);
    expect($cast->originalExpression)->toBe($expression);
})->with([
    ['boolean', CastType::Boolean],
    ['bool', CastType::Boolean],
    ['integer', CastType::Integer],
    ['int', CastType::Integer],
    ['float', CastType::Float],
    ['real', CastType::Float],
    ['double', CastType::Double],
    ['string', CastType::String],
    ['array', CastType::Array],
    ['json', CastType::Array],
    ['object', CastType::Object],
    ['collection', CastType::Collection],
]);

it('normalizes date and datetime casts including immutable variants', function (string $expression, CastType $expected) {
    $cast = $this->normalizer->normalize('column', $expression);

    expect($cast->type)->toBe($expected);
})->with([
    ['date', CastType::Date],
    ['datetime', CastType::DateTime],
    ['immutable_date', CastType::Date],
    ['immutable_datetime', CastType::DateTime],
    ['timestamp', CastType::Timestamp],
]);

it('preserves decimal scale metadata from decimal:n expressions', function () {
    $cast = $this->normalizer->normalize('price', 'decimal:2');

    expect($cast->type)->toBe(CastType::Decimal);
    expect($cast->scale)->toBe(2);
    expect($cast->originalExpression)->toBe('decimal:2');
});

it('recognizes php enum class casts', function () {
    $cast = $this->normalizer->normalize('status', AccountStatus::class);

    expect($cast->type)->toBe(CastType::Enum);
    expect($cast->customClass)->toBe(AccountStatus::class);
});

it('recognizes custom cast classes without misclassifying them as enums', function () {
    $cast = $this->normalizer->normalize('preferences', PreferencesCast::class);

    expect($cast->type)->toBe(CastType::Custom);
    expect($cast->customClass)->toBe(PreferencesCast::class);
});

it('maps unrecognized string casts to unknown', function () {
    $cast = $this->normalizer->normalize('password', 'hashed');

    expect($cast->type)->toBe(CastType::Unknown);
});

it('normalizes array cast definitions that reference a class', function () {
    $cast = $this->normalizer->normalize('preferences', [PreferencesCast::class]);

    expect($cast->type)->toBe(CastType::Custom);
    expect($cast->customClass)->toBe(PreferencesCast::class);
});
