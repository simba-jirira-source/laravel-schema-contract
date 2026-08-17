<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use SimbaJirira\SchemaContract\Analysis\ContractAnalyzer;
use SimbaJirira\SchemaContract\Discovery\EloquentModelDiscoverer;
use SimbaJirira\SchemaContract\Rules\RuleRegistry;
use SimbaJirira\SchemaContract\Support\IgnoreColumnMatcher;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\InvalidBooleanAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\InvalidBooleanCommandProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\ValidCommandProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Custom\Invoice;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Ignored\IgnoredRecord;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Standard\Article;

beforeEach(function (): void {
    $this->discoveryRoot = __DIR__.'/../../Fixtures/Discovery';
});

it('merges package defaults and allows host overrides for all settings', function () {
    expect(config('schema-contract.model_paths'))->toBeArray()->not->toBeEmpty();
    expect(config('schema-contract.ignore_models'))->toBeArray();
    expect(config('schema-contract.ignore_columns'))->toBeArray();

    config([
        'schema-contract.model_paths' => ['/custom/models'],
        'schema-contract.ignore_models' => ['App\\Models\\Legacy'],
        'schema-contract.ignore_columns' => ['users' => ['password']],
    ]);

    expect(config('schema-contract.model_paths'))->toBe(['/custom/models']);
    expect(config('schema-contract.ignore_models'))->toBe(['App\\Models\\Legacy']);
    expect(config('schema-contract.ignore_columns'))->toBe(['users' => ['password']]);
});

it('discovers models from custom configured paths', function () {
    config([
        'schema-contract.model_paths' => [
            $this->discoveryRoot.'/Custom',
        ],
    ]);

    expect((new EloquentModelDiscoverer)->discover())->toBe([
        Invoice::class,
    ]);
});

it('excludes ignored models from discovery results', function () {
    config([
        'schema-contract.model_paths' => [
            $this->discoveryRoot.'/Ignored',
            $this->discoveryRoot.'/Standard',
        ],
        'schema-contract.ignore_models' => [
            IgnoredRecord::class,
        ],
    ]);

    expect((new EloquentModelDiscoverer)->discover())->toBe([
        Article::class,
    ]);
});

it('skips ignored columns for the matching table during analysis', function () {
    $this->createAnalyzerTables();

    $analyzer = new ContractAnalyzer(
        ruleRegistry: RuleRegistry::withDefaults(),
        ignoreColumnMatcher: IgnoreColumnMatcher::fromConfigured([
            'analyzer_profiles' => ['active'],
        ]),
    );

    $result = $analyzer->analyzeModel(InvalidBooleanAnalyzerProfile::class);

    expect(collect($result->violations)->contains(
        fn ($violation) => $violation->column === 'active',
    ))->toBeFalse()
        ->and($result->columnsInspected)->toBeLessThan(
            ContractAnalyzer::withDefaults()->analyzeModel(InvalidBooleanAnalyzerProfile::class)->columnsInspected,
        );
});

it('does not skip columns when the ignored table name does not match', function () {
    $this->createAnalyzerTables();

    $analyzer = new ContractAnalyzer(
        ruleRegistry: RuleRegistry::withDefaults(),
        ignoreColumnMatcher: IgnoreColumnMatcher::fromConfigured([
            'other_profiles' => ['active'],
        ]),
    );

    $result = $analyzer->analyzeModel(InvalidBooleanAnalyzerProfile::class);

    expect($result->hasErrors())->toBeTrue()
        ->and(collect($result->violations)->contains(
            fn ($violation) => $violation->column === 'active',
        ))->toBeTrue();
});

it('applies configured ignored columns through the artisan command', function () {
    $this->createCommandCheckTables();

    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            ValidCommandProfile::class,
        ],
        'schema-contract.ignore_columns' => [
            'command_check_profiles' => ['active'],
        ],
    ]);

    $this->artisan('schema-contract:check', ['model' => InvalidBooleanCommandProfile::class])
        ->assertSuccessful()
        ->doesntExpectOutputToContain('ERROR    active')
        ->expectsOutputToContain('Models inspected: 1');
});

it('reads ignore column configuration from the application config at runtime', function () {
    $this->createCommandCheckTables();

    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            ValidCommandProfile::class,
        ],
        'schema-contract.ignore_columns' => [
            'command_check_profiles' => ['active'],
        ],
    ]);

    Artisan::call('schema-contract:check', ['model' => InvalidBooleanCommandProfile::class]);

    expect(Artisan::output())->not->toContain('ERROR    active');
});
