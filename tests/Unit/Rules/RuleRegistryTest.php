<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Rules\CastMatchesColumnTypeRule;
use SimbaJirira\SchemaContract\Rules\DateColumnHasCompatibleCastRule;
use SimbaJirira\SchemaContract\Rules\DecimalScaleMatchesRule;
use SimbaJirira\SchemaContract\Rules\JsonColumnHasCompatibleCastRule;
use SimbaJirira\SchemaContract\Rules\RuleRegistry;
use SimbaJirira\SchemaContract\Tests\Support\RuleTestFixtures as Fixtures;

it('registers the built-in rules by default', function () {
    $registry = RuleRegistry::withDefaults();

    expect($registry->rules())->toHaveCount(4)
        ->and($registry->rules()[0])->toBeInstanceOf(CastMatchesColumnTypeRule::class)
        ->and($registry->rules()[1])->toBeInstanceOf(DecimalScaleMatchesRule::class)
        ->and($registry->rules()[2])->toBeInstanceOf(JsonColumnHasCompatibleCastRule::class)
        ->and($registry->rules()[3])->toBeInstanceOf(DateColumnHasCompatibleCastRule::class);
});

it('aggregates violations from all registered rules', function () {
    $registry = RuleRegistry::withDefaults();

    $model = Fixtures::model([
        'credit_limit' => Fixtures::cast('credit_limit', CastType::Integer, 'integer'),
        'preferences' => Fixtures::cast('preferences', CastType::String, 'string'),
    ]);

    $creditLimitViolations = $registry->analyze(
        $model,
        Fixtures::column('credit_limit', DatabaseType::Decimal, scale: 2),
    );

    expect($creditLimitViolations)->toHaveCount(1)
        ->and($creditLimitViolations[0]->rule)->toBe(CastMatchesColumnTypeRule::IDENTIFIER);

    $preferencesViolations = $registry->analyze(
        $model,
        Fixtures::column('preferences', DatabaseType::Json),
    );

    expect($preferencesViolations)->toHaveCount(1)
        ->and($preferencesViolations[0]->rule)->toBe(JsonColumnHasCompatibleCastRule::IDENTIFIER);
});

it('allows additional rules to be registered', function () {
    $registry = RuleRegistry::withDefaults()->register(new DecimalScaleMatchesRule);

    expect($registry->rules())->toHaveCount(5);
});
