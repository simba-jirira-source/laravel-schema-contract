<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Rules;

use SimbaJirira\SchemaContract\Contracts\ContractRule;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\Support\ViolationFactory;

final class DecimalScaleMatchesRule implements ContractRule
{
    public const string IDENTIFIER = 'decimal_scale_matches';

    public function identifier(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(ModelDefinition $model, ColumnDefinition $column): array
    {
        if ($column->type !== DatabaseType::Decimal) {
            return [];
        }

        $cast = $model->casts[$column->name] ?? null;

        if ($cast?->type !== CastType::Decimal) {
            return [];
        }

        if ($column->scale === null || $cast->scale === null) {
            return [];
        }

        if ($column->scale === $cast->scale) {
            return [];
        }

        return [
            ViolationFactory::make(
                rule: self::IDENTIFIER,
                model: $model,
                column: $column,
                cast: $cast,
                severity: Severity::Error,
                message: sprintf(
                    'Decimal scale mismatch: database scale [%d] does not match cast scale [%d].',
                    $column->scale,
                    $cast->scale,
                ),
                suggestedCast: 'decimal:'.$column->scale,
                databaseScale: $column->scale,
                castScale: $cast->scale,
            ),
        ];
    }
}
