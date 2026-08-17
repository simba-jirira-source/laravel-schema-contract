<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\CastMatchesColumnTypeRule;
use SimbaJirira\SchemaContract\Tests\Support\RuleTestFixtures as Fixtures;

beforeEach(function (): void {
    $this->rule = new CastMatchesColumnTypeRule;
});

it('returns no violations when cast type matches the column type', function () {
    $model = Fixtures::model([
        'active' => Fixtures::cast('active', CastType::Boolean, 'boolean'),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('active', DatabaseType::Boolean),
    );

    expect($violations)->toBeEmpty();
});

it('returns an error violation for incompatible cast and column types', function () {
    $model = Fixtures::model([
        'credit_limit' => Fixtures::cast('credit_limit', CastType::Integer, 'integer'),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('credit_limit', DatabaseType::Decimal, scale: 2, precision: 10),
    );

    expect($violations)->toHaveCount(1);

    $violation = $violations[0];

    expect($violation->rule)->toBe(CastMatchesColumnTypeRule::IDENTIFIER)
        ->and($violation->severity)->toBe(Severity::Error)
        ->and($violation->modelClass)->toBe('App\\Models\\User')
        ->and($violation->table)->toBe('users')
        ->and($violation->connection)->toBe('mysql')
        ->and($violation->column)->toBe('credit_limit')
        ->and($violation->databaseType)->toBe(DatabaseType::Decimal)
        ->and($violation->castType)->toBe(CastType::Integer)
        ->and($violation->modelCast)->toBe('integer')
        ->and($violation->suggestedCast)->toBe('decimal:2')
        ->and($violation->databaseScale)->toBe(2);
});

it('warns when database type metadata is unsupported', function () {
    $model = Fixtures::model([
        'metadata' => Fixtures::cast('metadata', CastType::String, 'string'),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('metadata', DatabaseType::Unknown, originalDriverType: 'geography'),
    );

    expect($violations)->toHaveCount(1)
        ->and($violations[0]->severity)->toBe(Severity::Warning)
        ->and($violations[0]->message)->toContain('geography');
});

it('does not emit violations for custom casts', function () {
    $model = Fixtures::model([
        'payload' => Fixtures::cast(
            'payload',
            CastType::Custom,
            'App\\Casts\\PreferencesCast',
            customClass: 'App\\Casts\\PreferencesCast',
        ),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('payload', DatabaseType::String),
    );

    expect($violations)->toBeEmpty();
});

it('defers json columns to the json compatibility rule', function () {
    $model = Fixtures::model();

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('preferences', DatabaseType::Json),
    );

    expect($violations)->toBeEmpty();
});

it('defers decimal scale checks to the decimal scale rule', function () {
    $model = Fixtures::model([
        'price' => Fixtures::cast('price', CastType::Decimal, 'decimal:2', scale: 2),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('price', DatabaseType::Decimal, scale: 4),
    );

    expect($violations)->toBeEmpty();
});

it('does not print cli output', function () {
    $model = Fixtures::model([
        'credit_limit' => Fixtures::cast('credit_limit', CastType::Integer, 'integer'),
    ]);

    ob_start();
    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('credit_limit', DatabaseType::Decimal, scale: 2),
    );
    $output = ob_get_clean();

    expect($output)->toBe('')
        ->and($violations)->toHaveCount(1);
});
