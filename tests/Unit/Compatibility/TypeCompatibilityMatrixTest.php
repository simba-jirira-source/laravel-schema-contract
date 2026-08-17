<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Compatibility\TypeCompatibilityMatrix;
use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\CompatibilityResult;
use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;

beforeEach(function (): void {
    $this->matrix = new TypeCompatibilityMatrix;
});

function matrixColumn(DatabaseType $type, ?int $scale = null, ?string $originalDriverType = null): ColumnDefinition
{
    return new ColumnDefinition(
        name: 'column',
        type: $type,
        nullable: true,
        scale: $scale,
        originalDriverType: $originalDriverType,
    );
}

function matrixCast(CastType $type, ?int $scale = null, ?string $expression = null, ?string $customClass = null): CastDefinition
{
    return new CastDefinition(
        column: 'column',
        type: $type,
        originalExpression: $expression ?? $type->value,
        scale: $scale,
        customClass: $customClass,
    );
}

it('reports compatible pairings from the master spec matrix', function (
    DatabaseType $databaseType,
    CastType $castType,
    ?int $columnScale,
    ?int $castScale,
) {
    $result = $this->matrix->compare(
        matrixColumn($databaseType, $columnScale),
        matrixCast($castType, $castScale),
    );

    expect($result->state)->toBe(CompatibilityState::Compatible)
        ->and($result->isCompatible())->toBeTrue()
        ->and($result->suggestedSeverity)->toBeNull()
        ->and($result->databaseType)->toBe($databaseType)
        ->and($result->castType)->toBe($castType);
})->with([
    'boolean ↔ boolean' => [DatabaseType::Boolean, CastType::Boolean, null, null],
    'integer ↔ integer' => [DatabaseType::Integer, CastType::Integer, null, null],
    'bigint ↔ integer' => [DatabaseType::BigInteger, CastType::Integer, null, null],
    'smallint ↔ integer' => [DatabaseType::SmallInteger, CastType::Integer, null, null],
    'decimal ↔ decimal' => [DatabaseType::Decimal, CastType::Decimal, 2, 2],
    'float ↔ float' => [DatabaseType::Float, CastType::Float, null, null],
    'float ↔ double' => [DatabaseType::Float, CastType::Double, null, null],
    'double ↔ float' => [DatabaseType::Double, CastType::Float, null, null],
    'date ↔ date' => [DatabaseType::Date, CastType::Date, null, null],
    'datetime ↔ datetime' => [DatabaseType::DateTime, CastType::DateTime, null, null],
    'timestamp ↔ datetime' => [DatabaseType::Timestamp, CastType::DateTime, null, null],
    'timestamp ↔ timestamp' => [DatabaseType::Timestamp, CastType::Timestamp, null, null],
    'json ↔ array' => [DatabaseType::Json, CastType::Array, null, null],
    'json ↔ object' => [DatabaseType::Json, CastType::Object, null, null],
    'json ↔ collection' => [DatabaseType::Json, CastType::Collection, null, null],
    'uuid ↔ string' => [DatabaseType::Uuid, CastType::String, null, null],
    'string ↔ string' => [DatabaseType::String, CastType::String, null, null],
    'text ↔ string' => [DatabaseType::Text, CastType::String, null, null],
    'enum column ↔ string' => [DatabaseType::Enum, CastType::String, null, null],
    'enum column ↔ enum cast' => [DatabaseType::Enum, CastType::Enum, null, null],
    'string ↔ enum cast' => [DatabaseType::String, CastType::Enum, null, null],
]);

it('reports incompatible pairings with error severity and suggested casts', function (
    DatabaseType $databaseType,
    CastType $castType,
    string $expectedSuggestion,
) {
    $result = $this->matrix->compare(
        matrixColumn($databaseType, scale: $databaseType === DatabaseType::Decimal ? 2 : null),
        matrixCast($castType),
    );

    expect($result->state)->toBe(CompatibilityState::Incompatible)
        ->and($result->isCompatible())->toBeFalse()
        ->and($result->suggestedSeverity)->toBe(Severity::Error)
        ->and($result->suggestedCast)->toBe($expectedSuggestion);
})->with([
    'boolean ↔ integer' => [DatabaseType::Boolean, CastType::Integer, 'boolean'],
    'integer ↔ boolean' => [DatabaseType::Integer, CastType::Boolean, 'integer'],
    'decimal ↔ integer' => [DatabaseType::Decimal, CastType::Integer, 'decimal:2'],
    'decimal ↔ float' => [DatabaseType::Decimal, CastType::Float, 'decimal:2'],
    'json ↔ string' => [DatabaseType::Json, CastType::String, 'array'],
    'json ↔ integer' => [DatabaseType::Json, CastType::Integer, 'array'],
    'date ↔ integer' => [DatabaseType::Date, CastType::Integer, 'date'],
    'uuid ↔ integer' => [DatabaseType::Uuid, CastType::Integer, 'string'],
]);

it('detects decimal scale mismatches when both sides expose scale metadata', function () {
    $result = $this->matrix->compare(
        matrixColumn(DatabaseType::Decimal, scale: 4),
        matrixCast(CastType::Decimal, scale: 2, expression: 'decimal:2'),
    );

    expect($result->state)->toBe(CompatibilityState::Incompatible)
        ->and($result->suggestedSeverity)->toBe(Severity::Error)
        ->and($result->suggestedCast)->toBe('decimal:4')
        ->and($result->databaseScale)->toBe(4)
        ->and($result->castScale)->toBe(2);
});

it('accepts decimal casts when scale metadata is missing on one side', function () {
    $result = $this->matrix->compare(
        matrixColumn(DatabaseType::Decimal, scale: null),
        matrixCast(CastType::Decimal, scale: 2, expression: 'decimal:2'),
    );

    expect($result->state)->toBe(CompatibilityState::Compatible)
        ->and($result->suggestedSeverity)->toBeNull();
});

it('handles no-cast cases conservatively', function (
    DatabaseType $databaseType,
    CompatibilityState $expectedState,
    ?Severity $expectedSeverity,
    ?string $expectedSuggestion,
) {
    $result = $this->matrix->compare(matrixColumn($databaseType));

    expect($result->state)->toBe($expectedState)
        ->and($result->castType)->toBeNull()
        ->and($result->suggestedSeverity)->toBe($expectedSeverity)
        ->and($result->suggestedCast)->toBe($expectedSuggestion);
})->with([
    'json without cast warns' => [DatabaseType::Json, CompatibilityState::Uncertain, Severity::Warning, 'array'],
    'string without cast passes' => [DatabaseType::String, CompatibilityState::Compatible, null, null],
    'text without cast passes' => [DatabaseType::Text, CompatibilityState::Compatible, null, null],
    'uuid without cast passes' => [DatabaseType::Uuid, CompatibilityState::Compatible, null, null],
    'boolean without cast passes' => [DatabaseType::Boolean, CompatibilityState::Compatible, null, null],
    'integer without cast passes' => [DatabaseType::Integer, CompatibilityState::Compatible, null, null],
    'binary without cast is uncertain' => [DatabaseType::Binary, CompatibilityState::Uncertain, Severity::Info, null],
]);

it('skips comparison for unsupported database types', function () {
    $result = $this->matrix->compare(
        new ColumnDefinition(
            name: 'metadata',
            type: DatabaseType::Unknown,
            nullable: true,
            originalDriverType: 'geography',
        ),
        matrixCast(CastType::String),
    );

    expect($result->state)->toBe(CompatibilityState::Uncertain)
        ->and($result->suggestedSeverity)->toBe(Severity::Warning)
        ->and($result->reason)->toContain('geography');
});

it('treats custom casts as uncertain without failing inspection', function () {
    $result = $this->matrix->compare(
        matrixColumn(DatabaseType::Json),
        matrixCast(
            CastType::Custom,
            expression: 'App\\Casts\\PreferencesCast',
            customClass: 'App\\Casts\\PreferencesCast',
        ),
    );

    expect($result->state)->toBe(CompatibilityState::Uncertain)
        ->and($result->suggestedSeverity)->toBe(Severity::Info)
        ->and($result->castType)->toBe(CastType::Custom);
});

it('treats unrecognized cast expressions as uncertain', function () {
    $result = $this->matrix->compare(
        matrixColumn(DatabaseType::Integer),
        matrixCast(CastType::Unknown, expression: 'encrypted:json'),
    );

    expect($result->state)->toBe(CompatibilityState::Uncertain)
        ->and($result->suggestedSeverity)->toBe(Severity::Warning)
        ->and($result->reason)->toContain('encrypted:json');
});

it('treats binary columns with non-string casts as uncertain', function () {
    $result = $this->matrix->compare(
        matrixColumn(DatabaseType::Binary),
        matrixCast(CastType::Integer),
    );

    expect($result->state)->toBe(CompatibilityState::Uncertain)
        ->and($result->suggestedSeverity)->toBe(Severity::Info);
});

it('does not emit cli output from compatibility evaluation', function () {
    $result = $this->matrix->compare(
        matrixColumn(DatabaseType::Decimal, scale: 2),
        matrixCast(CastType::Integer),
    );

    expect($result)->toBeInstanceOf(CompatibilityResult::class)
        ->and($result->reason)->toBeString()
        ->and($result->reason)->not->toContain("\n");
});
