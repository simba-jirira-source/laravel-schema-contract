<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Compatibility;

use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\CompatibilityResult;
use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;

final class TypeCompatibilityMatrix
{
    public function compare(ColumnDefinition $column, ?CastDefinition $cast = null): CompatibilityResult
    {
        if ($column->type === DatabaseType::Unknown) {
            return $this->unknownDatabaseType($column);
        }

        if ($cast === null) {
            return $this->compareWithoutCast($column);
        }

        if ($cast->type === CastType::Custom) {
            return $this->customCast($column, $cast);
        }

        if ($cast->type === CastType::Unknown) {
            return $this->unknownCast($column, $cast);
        }

        if ($column->type === DatabaseType::Binary) {
            return $this->binaryColumn($column, $cast);
        }

        $compatibleCasts = $this->compatibleCastsFor($column->type);

        if (! in_array($cast->type, $compatibleCasts, true)) {
            return new CompatibilityResult(
                state: CompatibilityState::Incompatible,
                reason: sprintf(
                    'Cast type [%s] is incompatible with database type [%s].',
                    $cast->type->value,
                    $column->type->value,
                ),
                suggestedSeverity: Severity::Error,
                suggestedCast: $this->suggestCastFor($column),
                databaseType: $column->type,
                castType: $cast->type,
                databaseScale: $column->scale,
                castScale: $cast->scale,
            );
        }

        if ($column->type === DatabaseType::Decimal) {
            return $this->compareDecimalScale($column, $cast);
        }

        return new CompatibilityResult(
            state: CompatibilityState::Compatible,
            reason: sprintf(
                'Cast type [%s] is compatible with database type [%s].',
                $cast->type->value,
                $column->type->value,
            ),
            databaseType: $column->type,
            castType: $cast->type,
            databaseScale: $column->scale,
            castScale: $cast->scale,
        );
    }

    /**
     * @return list<CastType>
     */
    private function compatibleCastsFor(DatabaseType $databaseType): array
    {
        return match ($databaseType) {
            DatabaseType::Boolean => [CastType::Boolean],
            DatabaseType::Integer,
            DatabaseType::BigInteger,
            DatabaseType::SmallInteger => [CastType::Integer],
            DatabaseType::Decimal => [CastType::Decimal],
            DatabaseType::Float,
            DatabaseType::Double => [CastType::Float, CastType::Double],
            DatabaseType::String,
            DatabaseType::Text => [CastType::String, CastType::Enum],
            DatabaseType::Date => [CastType::Date],
            DatabaseType::DateTime,
            DatabaseType::Timestamp => [CastType::DateTime, CastType::Timestamp],
            DatabaseType::Json => [CastType::Array, CastType::Object, CastType::Collection],
            DatabaseType::Uuid => [CastType::String],
            DatabaseType::Enum => [CastType::String, CastType::Enum],
            DatabaseType::Binary,
            DatabaseType::Unknown => [],
        };
    }

    private function compareWithoutCast(ColumnDefinition $column): CompatibilityResult
    {
        if ($column->type === DatabaseType::Json) {
            return new CompatibilityResult(
                state: CompatibilityState::Uncertain,
                reason: 'JSON column has no explicit cast; consider declaring a compatible cast.',
                suggestedSeverity: Severity::Warning,
                suggestedCast: 'array',
                databaseType: $column->type,
            );
        }

        if (in_array($column->type, [DatabaseType::String, DatabaseType::Text, DatabaseType::Uuid], true)) {
            return new CompatibilityResult(
                state: CompatibilityState::Compatible,
                reason: sprintf(
                    'Database type [%s] accepts native string retrieval without an explicit cast.',
                    $column->type->value,
                ),
                databaseType: $column->type,
            );
        }

        if ($column->type === DatabaseType::Binary) {
            return new CompatibilityResult(
                state: CompatibilityState::Uncertain,
                reason: 'Binary column has no explicit cast; compatibility cannot be verified.',
                suggestedSeverity: Severity::Info,
                databaseType: $column->type,
            );
        }

        return new CompatibilityResult(
            state: CompatibilityState::Compatible,
            reason: sprintf(
                'No cast declared; native retrieval is acceptable for database type [%s].',
                $column->type->value,
            ),
            databaseType: $column->type,
        );
    }

    private function unknownDatabaseType(ColumnDefinition $column): CompatibilityResult
    {
        return new CompatibilityResult(
            state: CompatibilityState::Uncertain,
            reason: sprintf(
                'Unsupported database type [%s]; type comparison skipped.',
                $column->originalDriverType ?? DatabaseType::Unknown->value,
            ),
            suggestedSeverity: Severity::Warning,
            databaseType: $column->type,
        );
    }

    private function customCast(ColumnDefinition $column, CastDefinition $cast): CompatibilityResult
    {
        return new CompatibilityResult(
            state: CompatibilityState::Uncertain,
            reason: sprintf(
                'Custom cast [%s] declared; automatic type compatibility cannot be verified.',
                $cast->customClass ?? $cast->originalExpression ?? 'custom',
            ),
            suggestedSeverity: Severity::Info,
            databaseType: $column->type,
            castType: $cast->type,
        );
    }

    private function unknownCast(ColumnDefinition $column, CastDefinition $cast): CompatibilityResult
    {
        return new CompatibilityResult(
            state: CompatibilityState::Uncertain,
            reason: sprintf(
                'Unrecognized cast expression [%s]; compatibility cannot be verified.',
                $cast->originalExpression ?? 'unknown',
            ),
            suggestedSeverity: Severity::Warning,
            databaseType: $column->type,
            castType: $cast->type,
        );
    }

    private function binaryColumn(ColumnDefinition $column, CastDefinition $cast): CompatibilityResult
    {
        if ($cast->type === CastType::String) {
            return new CompatibilityResult(
                state: CompatibilityState::Uncertain,
                reason: 'Binary database column with string cast may be intentional; compatibility is uncertain.',
                suggestedSeverity: Severity::Info,
                databaseType: $column->type,
                castType: $cast->type,
            );
        }

        return new CompatibilityResult(
            state: CompatibilityState::Uncertain,
            reason: sprintf(
                'Binary database column with cast [%s]; compatibility cannot be verified.',
                $cast->type->value,
            ),
            suggestedSeverity: Severity::Info,
            databaseType: $column->type,
            castType: $cast->type,
        );
    }

    private function compareDecimalScale(ColumnDefinition $column, CastDefinition $cast): CompatibilityResult
    {
        if (
            $column->scale !== null
            && $cast->scale !== null
            && $column->scale !== $cast->scale
        ) {
            return new CompatibilityResult(
                state: CompatibilityState::Incompatible,
                reason: sprintf(
                    'Decimal scale mismatch: database scale [%d] does not match cast scale [%d].',
                    $column->scale,
                    $cast->scale,
                ),
                suggestedSeverity: Severity::Error,
                suggestedCast: $this->suggestCastFor($column),
                databaseType: $column->type,
                castType: $cast->type,
                databaseScale: $column->scale,
                castScale: $cast->scale,
            );
        }

        return new CompatibilityResult(
            state: CompatibilityState::Compatible,
            reason: sprintf(
                'Cast type [%s] is compatible with database type [%s].',
                $cast->type->value,
                $column->type->value,
            ),
            databaseType: $column->type,
            castType: $cast->type,
            databaseScale: $column->scale,
            castScale: $cast->scale,
        );
    }

    private function suggestCastFor(ColumnDefinition $column): ?string
    {
        return match ($column->type) {
            DatabaseType::Boolean => 'boolean',
            DatabaseType::Integer,
            DatabaseType::BigInteger,
            DatabaseType::SmallInteger => 'integer',
            DatabaseType::Decimal => $column->scale !== null
                ? 'decimal:'.$column->scale
                : 'decimal:2',
            DatabaseType::Float => 'float',
            DatabaseType::Double => 'double',
            DatabaseType::String,
            DatabaseType::Text,
            DatabaseType::Uuid => 'string',
            DatabaseType::Date => 'date',
            DatabaseType::DateTime => 'datetime',
            DatabaseType::Timestamp => 'datetime',
            DatabaseType::Json => 'array',
            DatabaseType::Enum => 'string',
            DatabaseType::Binary,
            DatabaseType::Unknown => null,
        };
    }
}
