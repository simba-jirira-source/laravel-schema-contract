<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Support;

use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;

final class RuleTestFixtures
{
    /**
     * @param  array<string, CastDefinition>  $casts
     */
    public static function model(array $casts = []): ModelDefinition
    {
        return new ModelDefinition(
            modelClass: 'App\\Models\\User',
            connection: 'mysql',
            table: 'users',
            primaryKey: 'id',
            casts: $casts,
        );
    }

    public static function column(
        string $name,
        DatabaseType $type,
        ?int $scale = null,
        ?int $precision = null,
        ?string $originalDriverType = null,
    ): ColumnDefinition {
        return new ColumnDefinition(
            name: $name,
            type: $type,
            nullable: true,
            precision: $precision,
            scale: $scale,
            originalDriverType: $originalDriverType,
        );
    }

    public static function cast(
        string $column,
        CastType $type,
        ?string $expression = null,
        ?int $scale = null,
        ?string $customClass = null,
    ): CastDefinition {
        return new CastDefinition(
            column: $column,
            type: $type,
            originalExpression: $expression ?? $type->value,
            scale: $scale,
            customClass: $customClass,
        );
    }
}
