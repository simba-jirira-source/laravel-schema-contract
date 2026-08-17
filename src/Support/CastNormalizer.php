<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use SimbaJirira\SchemaContract\DTO\CastDefinition;
use SimbaJirira\SchemaContract\Enums\CastType;
use UnitEnum;

final class CastNormalizer
{
    public function normalize(string $column, mixed $cast): CastDefinition
    {
        if (is_string($cast)) {
            return $this->normalizeStringCast($column, $cast);
        }

        if (is_array($cast)) {
            return $this->normalizeArrayCast($column, $cast);
        }

        return new CastDefinition(
            column: $column,
            type: CastType::Unknown,
            originalExpression: $this->stringifyCast($cast),
        );
    }

    private function normalizeStringCast(string $column, string $cast): CastDefinition
    {
        $cast = trim($cast);

        if (str_contains($cast, '\\') && class_exists($cast)) {
            return $this->normalizeClassCast($column, $cast);
        }

        if (str_starts_with(strtolower($cast), 'decimal:')) {
            $scale = (int) substr($cast, strlen('decimal:'));

            return new CastDefinition(
                column: $column,
                type: CastType::Decimal,
                originalExpression: $cast,
                scale: $scale,
            );
        }

        $type = match (strtolower($cast)) {
            'bool', 'boolean' => CastType::Boolean,
            'int', 'integer' => CastType::Integer,
            'float', 'real' => CastType::Float,
            'double' => CastType::Double,
            'decimal' => CastType::Decimal,
            'string' => CastType::String,
            'array' => CastType::Array,
            'json' => CastType::Array,
            'object' => CastType::Object,
            'collection' => CastType::Collection,
            'date' => CastType::Date,
            'datetime' => CastType::DateTime,
            'immutable_date' => CastType::Date,
            'immutable_datetime' => CastType::DateTime,
            'timestamp' => CastType::Timestamp,
            default => CastType::Unknown,
        };

        return new CastDefinition(
            column: $column,
            type: $type,
            originalExpression: $cast,
        );
    }

    /**
     * @param  array<int|string, mixed>  $cast
     */
    private function normalizeArrayCast(string $column, array $cast): CastDefinition
    {
        $first = $cast[0] ?? null;

        if (is_string($first) && class_exists($first)) {
            return $this->normalizeClassCast($column, $first, $this->stringifyCast($cast));
        }

        return new CastDefinition(
            column: $column,
            type: CastType::Unknown,
            originalExpression: $this->stringifyCast($cast),
        );
    }

    private function normalizeClassCast(string $column, string $class, ?string $originalExpression = null): CastDefinition
    {
        $originalExpression ??= $class;

        if (enum_exists($class) && is_subclass_of($class, UnitEnum::class)) {
            return new CastDefinition(
                column: $column,
                type: CastType::Enum,
                originalExpression: $originalExpression,
                customClass: $class,
            );
        }

        if (class_exists($class)) {
            $implements = class_implements($class) ?: [];

            if (
                isset($implements[CastsAttributes::class])
                || isset($implements[Castable::class])
            ) {
                return new CastDefinition(
                    column: $column,
                    type: CastType::Custom,
                    originalExpression: $originalExpression,
                    customClass: $class,
                );
            }
        }

        return new CastDefinition(
            column: $column,
            type: CastType::Custom,
            originalExpression: $originalExpression,
            customClass: $class,
        );
    }

    private function stringifyCast(mixed $cast): string
    {
        if (is_string($cast)) {
            return $cast;
        }

        if (is_array($cast)) {
            return implode(':', array_map(
                fn (mixed $part): string => is_string($part) ? $part : (is_object($part) ? $part::class : (string) $part),
                $cast,
            ));
        }

        if (is_object($cast)) {
            return $cast::class;
        }

        return (string) $cast;
    }
}
