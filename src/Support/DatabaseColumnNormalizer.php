<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\Enums\DatabaseType;

final class DatabaseColumnNormalizer
{
    public function normalize(RawColumnMetadata $raw): ColumnDefinition
    {
        $parsed = $this->parseDriverType($raw->driverType);

        return new ColumnDefinition(
            name: $raw->name,
            type: $this->mapToDatabaseType($parsed['base'], $parsed['length']),
            nullable: $raw->nullable,
            default: $raw->default,
            length: $raw->length ?? $parsed['length'],
            precision: $raw->precision ?? $parsed['precision'],
            scale: $raw->scale ?? $parsed['scale'],
            originalDriverType: $raw->driverType,
        );
    }

    /**
     * @return array{base: string, length: ?int, precision: ?int, scale: ?int}
     */
    private function parseDriverType(string $driverType): array
    {
        $normalized = strtolower(trim($driverType));
        $normalized = (string) preg_replace('/\s+(unsigned|zerofill)\b/', '', $normalized);

        $length = null;
        $precision = null;
        $scale = null;
        $base = $normalized;

        if (preg_match('/^(.+?)\(([^)]+)\)\s*$/', $normalized, $matches) === 1) {
            $base = trim($matches[1]);
            $parameters = array_map(trim(...), explode(',', $matches[2]));

            if (count($parameters) === 2 && is_numeric($parameters[0]) && is_numeric($parameters[1])) {
                $precision = (int) $parameters[0];
                $scale = (int) $parameters[1];
            } elseif (count($parameters) === 1 && is_numeric($parameters[0])) {
                $length = (int) $parameters[0];
            }
        }

        $base = $this->normalizeBaseType($base);

        return [
            'base' => $base,
            'length' => $length,
            'precision' => $precision,
            'scale' => $scale,
        ];
    }

    private function normalizeBaseType(string $base): string
    {
        return match (true) {
            str_starts_with($base, 'character varying') => 'varchar',
            str_starts_with($base, 'character') => 'char',
            str_starts_with($base, 'double precision') => 'double',
            str_starts_with($base, 'timestamp with time zone') => 'timestamptz',
            str_starts_with($base, 'timestamp without time zone') => 'timestamp',
            str_starts_with($base, 'time with time zone') => 'timetz',
            str_starts_with($base, 'time without time zone') => 'time',
            default => $base,
        };
    }

    private function mapToDatabaseType(string $base, ?int $length): DatabaseType
    {
        if ($base === 'tinyint' && $length === 1) {
            return DatabaseType::Boolean;
        }

        if ($base === 'bit' && ($length === null || $length === 1)) {
            return DatabaseType::Boolean;
        }

        return match ($base) {
            'bool', 'boolean' => DatabaseType::Boolean,
            'smallint', 'int2', 'tinyint' => DatabaseType::SmallInteger,
            'mediumint', 'int', 'integer', 'int4', 'serial' => DatabaseType::Integer,
            'bigint', 'int8', 'bigserial' => DatabaseType::BigInteger,
            'decimal', 'numeric', 'dec', 'fixed', 'number' => DatabaseType::Decimal,
            'float', 'real', 'float4' => DatabaseType::Float,
            'double', 'float8' => DatabaseType::Double,
            'varchar', 'char', 'character', 'nvarchar', 'nchar', 'string', 'bpchar' => DatabaseType::String,
            'text', 'tinytext', 'mediumtext', 'longtext', 'clob' => DatabaseType::Text,
            'date' => DatabaseType::Date,
            'datetime', 'datetime2' => DatabaseType::DateTime,
            'timestamp', 'timestamptz', 'datetimeoffset' => DatabaseType::Timestamp,
            'json', 'jsonb' => DatabaseType::Json,
            'uuid', 'uniqueidentifier' => DatabaseType::Uuid,
            'enum' => DatabaseType::Enum,
            'binary', 'varbinary', 'blob', 'bytea', 'raw', 'bit' => DatabaseType::Binary,
            default => DatabaseType::Unknown,
        };
    }
}
