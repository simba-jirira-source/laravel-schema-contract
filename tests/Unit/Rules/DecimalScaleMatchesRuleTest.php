<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\DecimalScaleMatchesRule;
use SimbaJirira\SchemaContract\Tests\Support\RuleTestFixtures as Fixtures;

beforeEach(function (): void {
    $this->rule = new DecimalScaleMatchesRule;
});

it('returns no violations when decimal scales match', function () {
    $model = Fixtures::model([
        'price' => Fixtures::cast('price', CastType::Decimal, 'decimal:2', scale: 2),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('price', DatabaseType::Decimal, scale: 2),
    );

    expect($violations)->toBeEmpty();
});

it('returns an error when decimal scales mismatch and both are known', function () {
    $model = Fixtures::model([
        'price' => Fixtures::cast('price', CastType::Decimal, 'decimal:2', scale: 2),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('price', DatabaseType::Decimal, scale: 4, precision: 12),
    );

    expect($violations)->toHaveCount(1);

    $violation = $violations[0];

    expect($violation->rule)->toBe(DecimalScaleMatchesRule::IDENTIFIER)
        ->and($violation->severity)->toBe(Severity::Error)
        ->and($violation->column)->toBe('price')
        ->and($violation->table)->toBe('users')
        ->and($violation->databaseType)->toBe(DatabaseType::Decimal)
        ->and($violation->castType)->toBe(CastType::Decimal)
        ->and($violation->modelCast)->toBe('decimal:2')
        ->and($violation->databaseScale)->toBe(4)
        ->and($violation->castScale)->toBe(2)
        ->and($violation->suggestedCast)->toBe('decimal:4');
});

it('skips scale comparison when metadata is incomplete', function () {
    $model = Fixtures::model([
        'price' => Fixtures::cast('price', CastType::Decimal, 'decimal:2', scale: 2),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('price', DatabaseType::Decimal, scale: null),
    );

    expect($violations)->toBeEmpty();
});

it('ignores non-decimal columns and non-decimal casts', function () {
    $model = Fixtures::model([
        'quantity' => Fixtures::cast('quantity', CastType::Integer, 'integer'),
    ]);

    expect($this->rule->analyze(
        $model,
        Fixtures::column('quantity', DatabaseType::Integer),
    ))->toBeEmpty();

    $decimalModel = Fixtures::model([
        'price' => Fixtures::cast('price', CastType::Integer, 'integer'),
    ]);

    expect($this->rule->analyze(
        $decimalModel,
        Fixtures::column('price', DatabaseType::Decimal, scale: 2),
    ))->toBeEmpty();
});
