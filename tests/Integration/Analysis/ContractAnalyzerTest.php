<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use SimbaJirira\SchemaContract\Analysis\ContractAnalyzer;
use SimbaJirira\SchemaContract\DTO\AnalysisResult;
use SimbaJirira\SchemaContract\DTO\ContractResult;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\CastMatchesColumnTypeRule;
use SimbaJirira\SchemaContract\Rules\DateColumnHasCompatibleCastRule;
use SimbaJirira\SchemaContract\Rules\DecimalScaleMatchesRule;
use SimbaJirira\SchemaContract\Rules\JsonColumnHasCompatibleCastRule;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\DecimalScaleMismatchAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\InvalidBooleanAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\InvalidDecimalAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\JsonWarningAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\LegacyAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\MissingTableAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\UnknownTypeAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\ValidAnalyzerProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Analysis\ValidDateAnalyzerProfile;

beforeEach(function (): void {
    $this->analyzer = ContractAnalyzer::withDefaults();
    $this->createAnalyzerTables();
});

function analyzerViolationColumns(ContractResult $result): array
{
    return array_values(array_unique(array_map(
        fn ($violation) => $violation->column,
        $result->violations,
    )));
}

it('reports a clean result for a fully compatible model', function () {
    $result = $this->analyzer->analyzeModel(ValidAnalyzerProfile::class);

    expect($result)->toBeInstanceOf(ContractResult::class)
        ->and($result->hasErrors())->toBeFalse()
        ->and($result->hasWarnings())->toBeFalse()
        ->and($result->errors())->toBeEmpty()
        ->and($result->warnings())->toBeEmpty()
        ->and($result->passed())->toBeGreaterThan(0)
        ->and($result->columnsInspected)->toBe($result->passed());
});

it('detects invalid boolean casts as errors', function () {
    $result = $this->analyzer->analyzeModel(InvalidBooleanAnalyzerProfile::class);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors())->toHaveCount(1)
        ->and($result->errors()[0]->column)->toBe('active')
        ->and($result->errors()[0]->rule)->toBe(CastMatchesColumnTypeRule::IDENTIFIER)
        ->and($result->errors()[0]->severity)->toBe(Severity::Error);
});

it('detects invalid decimal casts as errors', function () {
    $result = $this->analyzer->analyzeModel(InvalidDecimalAnalyzerProfile::class);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors())->toHaveCount(1)
        ->and($result->errors()[0]->column)->toBe('price')
        ->and($result->errors()[0]->rule)->toBe(CastMatchesColumnTypeRule::IDENTIFIER)
        ->and($result->errors()[0]->suggestedCast)->toBe('decimal:2');
});

it('detects decimal scale mismatches as errors', function () {
    Schema::connection('testing')->getConnection()->statement(
        'ALTER TABLE analyzer_profiles ADD COLUMN bonus DECIMAL(10,4) NULL',
    );

    $result = $this->analyzer->analyzeModel(DecimalScaleMismatchAnalyzerProfile::class);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->errors()[0]->column)->toBe('bonus')
        ->and($result->errors()[0]->rule)->toBe(DecimalScaleMatchesRule::IDENTIFIER)
        ->and($result->errors()[0]->databaseScale)->toBe(4)
        ->and($result->errors()[0]->castScale)->toBe(2);
});

it('warns when json columns have no compatible cast', function () {
    $result = $this->analyzer->analyzeModel(JsonWarningAnalyzerProfile::class);

    expect($result->hasErrors())->toBeFalse()
        ->and($result->hasWarnings())->toBeTrue()
        ->and($result->warnings())->toHaveCount(1)
        ->and($result->warnings()[0]->column)->toBe('payload')
        ->and($result->warnings()[0]->rule)->toBe(JsonColumnHasCompatibleCastRule::IDENTIFIER)
        ->and($result->warnings()[0]->suggestedCast)->toBe('array');
});

it('passes valid date and datetime casts', function () {
    $result = $this->analyzer->analyzeModel(ValidDateAnalyzerProfile::class);

    expect($result->hasErrors())->toBeFalse()
        ->and($result->errors())->toBeEmpty()
        ->and(collect($result->violations)->contains(
            fn ($violation) => in_array($violation->column, ['starts_on', 'published_at'], true)
                && $violation->rule === DateColumnHasCompatibleCastRule::IDENTIFIER,
        ))->toBeFalse();
});

it('analyzes models with custom table names', function () {
    $result = $this->analyzer->analyzeModel(LegacyAnalyzerProfile::class);

    expect($result->table?->name)->toBe('legacy_profiles')
        ->and($result->hasErrors())->toBeFalse()
        ->and($result->columnsInspected)->toBeGreaterThan(0);
});

it('handles missing tables with a structured error result', function () {
    $result = $this->analyzer->analyzeModel(MissingTableAnalyzerProfile::class);

    expect($result->hasErrors())->toBeTrue()
        ->and($result->table)->toBeNull()
        ->and($result->columnsInspected)->toBe(0)
        ->and($result->errors())->toHaveCount(1)
        ->and($result->errors()[0]->rule)->toBe(ContractAnalyzer::MISSING_TABLE_RULE)
        ->and($result->errors()[0]->table)->toBe('analyzer_missing_profiles');
});

it('warns when a column uses an unsupported database type', function () {
    Schema::connection('testing')->getConnection()->statement(
        'ALTER TABLE analyzer_profiles ADD COLUMN legacy_flag WEIRD_TYPE NULL',
    );

    $result = $this->analyzer->analyzeModel(UnknownTypeAnalyzerProfile::class);

    expect($result->hasWarnings())->toBeTrue()
        ->and(collect($result->warnings())->contains(
            fn ($violation) => $violation->column === 'legacy_flag'
                && $violation->rule === CastMatchesColumnTypeRule::IDENTIFIER,
        ))->toBeTrue();
});

it('aggregates deterministic summaries across multiple models', function () {
    $analysis = $this->analyzer->analyzeModels([
        InvalidDecimalAnalyzerProfile::class,
        ValidAnalyzerProfile::class,
        JsonWarningAnalyzerProfile::class,
    ]);

    expect($analysis)->toBeInstanceOf(AnalysisResult::class)
        ->and($analysis->summary->modelsInspected)->toBe(3)
        ->and($analysis->summary->errorCount)->toBe(1)
        ->and($analysis->summary->warningCount)->toBe(1)
        ->and($analysis->hasErrors())->toBeTrue()
        ->and($analysis->hasWarnings())->toBeTrue()
        ->and($analysis->errors())->toHaveCount(1)
        ->and($analysis->warnings())->toHaveCount(1)
        ->and($analysis->results[0]->modelClass())->toBe(InvalidDecimalAnalyzerProfile::class)
        ->and($analysis->results[1]->modelClass())->toBe(JsonWarningAnalyzerProfile::class)
        ->and($analysis->results[2]->modelClass())->toBe(ValidAnalyzerProfile::class);
});

it('does not print cli output during analysis', function () {
    ob_start();
    $result = $this->analyzer->analyzeModel(InvalidBooleanAnalyzerProfile::class);
    $output = ob_get_clean();

    expect($output)->toBe('')
        ->and($result->violations)->not->toBeEmpty();
});

it('skips ignored columns during analysis', function () {
    $analyzer = new ContractAnalyzer(ignoreColumns: ['active']);

    $result = $analyzer->analyzeModel(InvalidBooleanAnalyzerProfile::class);

    expect(collect($result->violations)->contains(
        fn ($violation) => $violation->column === 'active',
    ))->toBeFalse();
});
