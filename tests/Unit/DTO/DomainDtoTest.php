<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\CompatibilityResult;
use SimbaJirira\SchemaContract\DTO\ContractViolation;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\DTO\TableDefinition;
use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;

it('builds a column definition with nullable metadata and precision', function () {
    $column = new ColumnDefinition(
        name: 'credit_limit',
        type: DatabaseType::Decimal,
        nullable: true,
        default: null,
        precision: 10,
        scale: 2,
        originalDriverType: 'decimal(10,2)',
    );

    expect($column->name)->toBe('credit_limit');
    expect($column->type)->toBe(DatabaseType::Decimal);
    expect($column->nullable)->toBeTrue();
    expect($column->default)->toBeNull();
    expect($column->precision)->toBe(10);
    expect($column->scale)->toBe(2);
    expect($column->originalDriverType)->toBe('decimal(10,2)');
});

it('builds a column definition with length and non-null default', function () {
    $column = new ColumnDefinition(
        name: 'name',
        type: DatabaseType::String,
        nullable: false,
        default: 'guest',
        length: 255,
        originalDriverType: 'varchar(255)',
    );

    expect($column->nullable)->toBeFalse();
    expect($column->default)->toBe('guest');
    expect($column->length)->toBe(255);
    expect($column->precision)->toBeNull();
    expect($column->scale)->toBeNull();
});

it('builds a cast definition preserving decimal scale metadata', function () {
    $cast = new CastDefinition(
        column: 'credit_limit',
        type: CastType::Decimal,
        originalExpression: 'decimal:2',
        scale: 2,
    );

    expect($cast->column)->toBe('credit_limit');
    expect($cast->type)->toBe(CastType::Decimal);
    expect($cast->originalExpression)->toBe('decimal:2');
    expect($cast->scale)->toBe(2);
    expect($cast->customClass)->toBeNull();
});

it('builds a custom cast definition with class metadata', function () {
    $cast = new CastDefinition(
        column: 'preferences',
        type: CastType::Custom,
        originalExpression: 'App\\Casts\\PreferencesCast',
        customClass: 'App\\Casts\\PreferencesCast',
    );

    expect($cast->type)->toBe(CastType::Custom);
    expect($cast->customClass)->toBe('App\\Casts\\PreferencesCast');
});

it('builds a table definition from typed column definitions', function () {
    $table = new TableDefinition(
        name: 'users',
        connection: 'mysql',
        columns: [
            new ColumnDefinition('id', DatabaseType::BigInteger, nullable: false),
            new ColumnDefinition('email', DatabaseType::String, nullable: true, length: 255),
        ],
    );

    expect($table->name)->toBe('users');
    expect($table->connection)->toBe('mysql');
    expect($table->columns)->toHaveCount(2);
    expect($table->columns[0])->toBeInstanceOf(ColumnDefinition::class);
    expect($table->columns[1]->nullable)->toBeTrue();
});

it('builds a model definition with keyed cast definitions', function () {
    $model = new ModelDefinition(
        modelClass: 'App\\Models\\User',
        connection: 'pgsql',
        table: 'users',
        primaryKey: 'id',
        casts: [
            'active' => new CastDefinition('active', CastType::Boolean, originalExpression: 'boolean'),
            'credit_limit' => new CastDefinition('credit_limit', CastType::Integer, originalExpression: 'integer'),
        ],
    );

    expect($model->modelClass)->toBe('App\\Models\\User');
    expect($model->connection)->toBe('pgsql');
    expect($model->table)->toBe('users');
    expect($model->primaryKey)->toBe('id');
    expect($model->casts)->toHaveCount(2);
    expect($model->casts['credit_limit']->type)->toBe(CastType::Integer);
});

it('builds a contract violation with severity and scale metadata', function () {
    $violation = new ContractViolation(
        severity: Severity::Error,
        modelClass: 'App\\Models\\User',
        column: 'credit_limit',
        message: 'Cast type integer is incompatible with database decimal(10,2).',
        suggestedCast: 'decimal:2',
        databaseType: DatabaseType::Decimal,
        castType: CastType::Integer,
        databasePrecision: 10,
        databaseScale: 2,
    );

    expect($violation->severity)->toBe(Severity::Error);
    expect($violation->column)->toBe('credit_limit');
    expect($violation->suggestedCast)->toBe('decimal:2');
    expect($violation->databaseType)->toBe(DatabaseType::Decimal);
    expect($violation->castType)->toBe(CastType::Integer);
    expect($violation->databasePrecision)->toBe(10);
    expect($violation->databaseScale)->toBe(2);
    expect($violation->castScale)->toBeNull();
});

it('builds a warning violation for missing json cast metadata', function () {
    $violation = new ContractViolation(
        severity: Severity::Warning,
        modelClass: 'App\\Models\\User',
        column: 'preferences',
        message: 'JSON column has no compatible cast.',
        suggestedCast: 'array',
        databaseType: DatabaseType::Json,
    );

    expect($violation->severity)->toBe(Severity::Warning);
    expect($violation->castType)->toBeNull();
    expect($violation->databaseType)->toBe(DatabaseType::Json);
});

it('builds a compatibility result with severity and scale metadata', function () {
    $result = new CompatibilityResult(
        state: CompatibilityState::Incompatible,
        reason: 'Cast type integer is incompatible with database decimal.',
        suggestedSeverity: Severity::Error,
        suggestedCast: 'decimal:2',
        databaseType: DatabaseType::Decimal,
        castType: CastType::Integer,
        databaseScale: 2,
        castScale: null,
    );

    expect($result->isCompatible())->toBeFalse()
        ->and($result->suggestedSeverity)->toBe(Severity::Error)
        ->and($result->databaseScale)->toBe(2);
});

it('keeps domain dto properties readonly', function () {
    $column = new ColumnDefinition('amount', DatabaseType::Decimal, nullable: false);

    expect(function () use ($column): void {
        $column->name = 'changed';
    })->toThrow(Error::class);
});
