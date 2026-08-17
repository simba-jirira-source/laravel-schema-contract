<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Rules\Support;

use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\CompatibilityResult;
use SimbaJirira\SchemaContract\DTO\ContractViolation;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\Severity;

final class ViolationFactory
{
    public static function fromCompatibilityResult(
        string $rule,
        ModelDefinition $model,
        ColumnDefinition $column,
        ?CastDefinition $cast,
        CompatibilityResult $result,
    ): ContractViolation {
        $severity = match ($result->state) {
            CompatibilityState::Incompatible => Severity::Error,
            CompatibilityState::Uncertain => $result->suggestedSeverity ?? Severity::Warning,
            default => Severity::Warning,
        };

        return new ContractViolation(
            severity: $severity,
            rule: $rule,
            modelClass: $model->modelClass,
            table: $model->table,
            connection: $model->connection,
            column: $column->name,
            message: $result->reason,
            suggestedCast: $result->suggestedCast,
            databaseType: $result->databaseType ?? $column->type,
            castType: $result->castType ?? $cast?->type,
            modelCast: $cast?->originalExpression,
            databasePrecision: $column->precision,
            databaseScale: $result->databaseScale ?? $column->scale,
            castScale: $result->castScale ?? $cast?->scale,
        );
    }

    public static function make(
        string $rule,
        ModelDefinition $model,
        ColumnDefinition $column,
        ?CastDefinition $cast,
        Severity $severity,
        string $message,
        ?string $suggestedCast = null,
        ?int $databaseScale = null,
        ?int $castScale = null,
    ): ContractViolation {
        return new ContractViolation(
            severity: $severity,
            rule: $rule,
            modelClass: $model->modelClass,
            table: $model->table,
            connection: $model->connection,
            column: $column->name,
            message: $message,
            suggestedCast: $suggestedCast,
            databaseType: $column->type,
            castType: $cast?->type,
            modelCast: $cast?->originalExpression,
            databasePrecision: $column->precision,
            databaseScale: $databaseScale ?? $column->scale,
            castScale: $castScale ?? $cast?->scale,
        );
    }
}
