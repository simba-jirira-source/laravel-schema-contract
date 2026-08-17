<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\TableDefinition;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Exceptions\MissingTableException;
use SimbaJirira\SchemaContract\Inspectors\EloquentModelInspector;
use SimbaJirira\SchemaContract\Inspectors\EloquentSchemaInspector;
use SimbaJirira\SchemaContract\Tests\Fixtures\Schema\LegacyProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Schema\MissingTableProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Schema\Profile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Schema\RemoteProfile;

beforeEach(function (): void {
    $this->modelInspector = new EloquentModelInspector;
    $this->schemaInspector = new EloquentSchemaInspector;
    $this->createSchemaInspectionTables();
});

function schemaColumn(TableDefinition $table, string $name): ColumnDefinition
{
    foreach ($table->columns as $column) {
        if ($column->name === $name) {
            return $column;
        }
    }

    throw new InvalidArgumentException("Column [{$name}] was not found on table [{$table->name}].");
}

it('inspects normalized column types from a temporary sqlite schema', function () {
    $model = $this->modelInspector->inspect(Profile::class);
    $table = $this->schemaInspector->inspect($model);

    expect($table->name)->toBe('schema_inspection_profiles')
        ->and($table->connection)->toBe('testing')
        ->and(schemaColumn($table, 'active')->type)->toBe(DatabaseType::Boolean)
        ->and(schemaColumn($table, 'quantity')->type)->toBe(DatabaseType::Integer)
        ->and(schemaColumn($table, 'price')->type)->toBe(DatabaseType::Decimal)
        ->and(schemaColumn($table, 'label')->type)->toBe(DatabaseType::String)
        ->and(schemaColumn($table, 'bio')->type)->toBe(DatabaseType::Text)
        ->and(schemaColumn($table, 'payload')->type)->toBe(DatabaseType::Json)
        ->and(schemaColumn($table, 'starts_on')->type)->toBe(DatabaseType::Date)
        ->and(schemaColumn($table, 'published_at')->type)->toBe(DatabaseType::DateTime);
});

it('preserves nullable and default metadata when exposed by the schema api', function () {
    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(Profile::class),
    );

    expect(schemaColumn($table, 'price')->nullable)->toBeTrue()
        ->and(schemaColumn($table, 'label')->nullable)->toBeFalse()
        ->and(schemaColumn($table, 'label')->default)->not->toBeNull()
        ->and(schemaColumn($table, 'bio')->nullable)->toBeTrue();
});

it('preserves decimal precision and scale metadata when available', function () {
    Schema::connection('testing')->getConnection()->statement(
        'ALTER TABLE schema_inspection_profiles ADD COLUMN bonus DECIMAL(10,2) NULL',
    );

    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(Profile::class),
    );

    $bonus = schemaColumn($table, 'bonus');
    $price = schemaColumn($table, 'price');

    expect($bonus->type)->toBe(DatabaseType::Decimal)
        ->and($bonus->precision)->toBe(10)
        ->and($bonus->scale)->toBe(2)
        ->and($bonus->originalDriverType)->not->toBe('')
        ->and($price->type)->toBe(DatabaseType::Decimal)
        ->and($price->precision)->toBeNull()
        ->and($price->scale)->toBeNull();
});

it('respects custom table names during schema inspection', function () {
    $model = $this->modelInspector->inspect(LegacyProfile::class);
    $table = $this->schemaInspector->inspect($model);

    expect($table->name)->toBe('legacy_profiles')
        ->and(schemaColumn($table, 'code')->type)->toBe(DatabaseType::String);
});

it('respects custom connections during schema inspection', function () {
    $model = $this->modelInspector->inspect(RemoteProfile::class);
    $table = $this->schemaInspector->inspect($model);

    expect($table->connection)->toBe('analytics')
        ->and($table->name)->toBe('remote_profiles')
        ->and(schemaColumn($table, 'name')->type)->toBe(DatabaseType::String);
});

it('throws a dedicated exception when the model table is missing', function () {
    $model = $this->modelInspector->inspect(MissingTableProfile::class);

    expect(fn () => $this->schemaInspector->inspect($model))
        ->toThrow(MissingTableException::class);
});

it('maps unsupported driver metadata to unknown without crashing inspection', function () {
    Schema::connection('testing')->getConnection()->statement(
        'ALTER TABLE schema_inspection_profiles ADD COLUMN legacy_flag WEIRD_TYPE NULL',
    );

    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(Profile::class),
    );

    expect(schemaColumn($table, 'legacy_flag')->type)->toBe(DatabaseType::Unknown);
});

it('retains original driver type metadata on normalized columns', function () {
    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(Profile::class),
    );

    expect(schemaColumn($table, 'price')->originalDriverType)->not->toBe('')
        ->and(schemaColumn($table, 'payload')->originalDriverType)->not->toBe('');
});

it('returns typed column definitions without exposing raw schema arrays outside normalization boundaries', function () {
    $table = $this->schemaInspector->inspect(
        $this->modelInspector->inspect(Profile::class),
    );

    foreach ($table->columns as $column) {
        expect($column)->toBeInstanceOf(ColumnDefinition::class);
    }
});
