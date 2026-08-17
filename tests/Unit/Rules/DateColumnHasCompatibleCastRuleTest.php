<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\DateColumnHasCompatibleCastRule;
use SimbaJirira\SchemaContract\Tests\Support\RuleTestFixtures as Fixtures;

beforeEach(function (): void {
    $this->rule = new DateColumnHasCompatibleCastRule;
});

it('returns no violations for compatible date and datetime casts', function (
    DatabaseType $databaseType,
    CastType $castType,
    string $expression,
) {
    $model = Fixtures::model([
        'starts_on' => Fixtures::cast('starts_on', $castType, $expression),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('starts_on', $databaseType),
    );

    expect($violations)->toBeEmpty();
})->with([
    'date column with date cast' => [DatabaseType::Date, CastType::Date, 'date'],
    'datetime column with datetime cast' => [DatabaseType::DateTime, CastType::DateTime, 'datetime'],
    'timestamp column with timestamp cast' => [DatabaseType::Timestamp, CastType::Timestamp, 'timestamp'],
    'timestamp column with datetime cast' => [DatabaseType::Timestamp, CastType::DateTime, 'datetime'],
]);

it('errors when date columns use incompatible casts', function () {
    $model = Fixtures::model([
        'starts_on' => Fixtures::cast('starts_on', CastType::Integer, 'integer'),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('starts_on', DatabaseType::Date),
    );

    expect($violations)->toHaveCount(1)
        ->and($violations[0]->rule)->toBe(DateColumnHasCompatibleCastRule::IDENTIFIER)
        ->and($violations[0]->severity)->toBe(Severity::Error)
        ->and($violations[0]->suggestedCast)->toBe('date');
});

it('does not emit noisy warnings for standard timestamp columns', function () {
    $model = Fixtures::model();

    foreach (['created_at', 'updated_at'] as $column) {
        expect($this->rule->analyze(
            $model,
            Fixtures::column($column, DatabaseType::Timestamp),
        ))->toBeEmpty();
    }
});

it('allows non-standard timestamp columns without casts', function () {
    $model = Fixtures::model();

    expect($this->rule->analyze(
        $model,
        Fixtures::column('published_at', DatabaseType::DateTime),
    ))->toBeEmpty();
});

it('ignores non-date columns', function () {
    $model = Fixtures::model([
        'active' => Fixtures::cast('active', CastType::Boolean, 'boolean'),
    ]);

    expect($this->rule->analyze(
        $model,
        Fixtures::column('active', DatabaseType::Boolean),
    ))->toBeEmpty();
});
