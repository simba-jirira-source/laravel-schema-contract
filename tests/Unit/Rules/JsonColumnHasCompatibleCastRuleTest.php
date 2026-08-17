<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\JsonColumnHasCompatibleCastRule;
use SimbaJirira\SchemaContract\Tests\Support\RuleTestFixtures as Fixtures;

beforeEach(function (): void {
    $this->rule = new JsonColumnHasCompatibleCastRule;
});

it('returns no violations when json columns have compatible casts', function (CastType $castType, string $expression) {
    $model = Fixtures::model([
        'preferences' => Fixtures::cast('preferences', $castType, $expression),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('preferences', DatabaseType::Json),
    );

    expect($violations)->toBeEmpty();
})->with([
    'array cast' => [CastType::Array, 'array'],
    'object cast' => [CastType::Object, 'object'],
    'collection cast' => [CastType::Collection, 'collection'],
]);

it('warns when a json column has no explicit cast', function () {
    $model = Fixtures::model();

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('preferences', DatabaseType::Json),
    );

    expect($violations)->toHaveCount(1)
        ->and($violations[0]->rule)->toBe(JsonColumnHasCompatibleCastRule::IDENTIFIER)
        ->and($violations[0]->severity)->toBe(Severity::Warning)
        ->and($violations[0]->suggestedCast)->toBe('array')
        ->and($violations[0]->castType)->toBeNull()
        ->and($violations[0]->modelCast)->toBeNull();
});

it('errors when a json column has an incompatible cast', function () {
    $model = Fixtures::model([
        'preferences' => Fixtures::cast('preferences', CastType::String, 'string'),
    ]);

    $violations = $this->rule->analyze(
        $model,
        Fixtures::column('preferences', DatabaseType::Json),
    );

    expect($violations)->toHaveCount(1)
        ->and($violations[0]->severity)->toBe(Severity::Error)
        ->and($violations[0]->castType)->toBe(CastType::String)
        ->and($violations[0]->suggestedCast)->toBe('array');
});

it('ignores non-json columns', function () {
    $model = Fixtures::model([
        'name' => Fixtures::cast('name', CastType::String, 'string'),
    ]);

    expect($this->rule->analyze(
        $model,
        Fixtures::column('name', DatabaseType::String),
    ))->toBeEmpty();
});

it('does not flag custom json casts as incompatible', function () {
    $model = Fixtures::model([
        'preferences' => Fixtures::cast(
            'preferences',
            CastType::Custom,
            'App\\Casts\\PreferencesCast',
            customClass: 'App\\Casts\\PreferencesCast',
        ),
    ]);

    expect($this->rule->analyze(
        $model,
        Fixtures::column('preferences', DatabaseType::Json),
    ))->toBeEmpty();
});
