<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Inspectors\EloquentModelInspector;
use SimbaJirira\SchemaContract\Tests\Fixtures\Casts\PreferencesCast;
use SimbaJirira\SchemaContract\Tests\Fixtures\Enums\AccountStatus;
use SimbaJirira\SchemaContract\Tests\Fixtures\Models\CustomConnectionModel;
use SimbaJirira\SchemaContract\Tests\Fixtures\Models\CustomTableModel;
use SimbaJirira\SchemaContract\Tests\Fixtures\Models\FullyCastedModel;
use SimbaJirira\SchemaContract\Tests\Fixtures\Models\StandardModel;

beforeEach(function (): void {
    $this->inspector = new EloquentModelInspector;
});

it('inspects model class table and primary key metadata', function () {
    $definition = $this->inspector->inspect(StandardModel::class);

    expect($definition->modelClass)->toBe(StandardModel::class);
    expect($definition->table)->toBe('standard_models');
    expect($definition->primaryKey)->toBe('id');
    expect($definition->connection)->not->toBe('');
});

it('respects a custom table name', function () {
    $definition = $this->inspector->inspect(CustomTableModel::class);

    expect($definition->table)->toBe('legacy_users');
});

it('respects a custom connection name', function () {
    $definition = $this->inspector->inspect(CustomConnectionModel::class);

    expect($definition->connection)->toBe('analytics');
    expect($definition->table)->toBe('remote_records');
});

it('normalizes scalar decimal json and date casts from the model', function () {
    $definition = $this->inspector->inspect(FullyCastedModel::class);

    expect($definition->casts['active']->type)->toBe(CastType::Boolean);
    expect($definition->casts['quantity']->type)->toBe(CastType::Integer);
    expect($definition->casts['ratio']->type)->toBe(CastType::Float);
    expect($definition->casts['weight']->type)->toBe(CastType::Double);
    expect($definition->casts['price']->type)->toBe(CastType::Decimal);
    expect($definition->casts['price']->scale)->toBe(2);
    expect($definition->casts['payload']->type)->toBe(CastType::Array);
    expect($definition->casts['starts_on']->type)->toBe(CastType::Date);
    expect($definition->casts['published_at']->type)->toBe(CastType::DateTime);
    expect($definition->casts['archived_on']->type)->toBe(CastType::Date);
    expect($definition->casts['locked_at']->type)->toBe(CastType::DateTime);
    expect($definition->casts['seen_at']->type)->toBe(CastType::Timestamp);
});

it('recognizes enum and custom cast classes from the model', function () {
    $definition = $this->inspector->inspect(FullyCastedModel::class);

    expect($definition->casts['status']->type)->toBe(CastType::Enum);
    expect($definition->casts['status']->customClass)->toBe(AccountStatus::class);
    expect($definition->casts['preferences']->type)->toBe(CastType::Custom);
    expect($definition->casts['preferences']->customClass)->toBe(PreferencesCast::class);
});

it('rejects non-model classes', function () {
    $this->inspector->inspect(stdClass::class);
})->throws(InvalidArgumentException::class);
