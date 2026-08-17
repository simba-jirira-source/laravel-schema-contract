<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\Enums\DatabaseType;

final class DatabaseColumnNormalizer
{
    public function __construct(
        private readonly ColumnTypeParser $typeParser = new ColumnTypeParser,
    ) {}

    public function normalize(RawColumnMetadata $raw): ColumnDefinition
    {
        $parsed = $this->typeParser->parse($raw->driverType);

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
            'varchar', 'char', 'character', 'nvarchar', 'nchar', 'string', 'bpchar', 'citext' => DatabaseType::String,
            'text', 'tinytext', 'mediumtext', 'longtext', 'clob' => DatabaseType::Text,
            'date' => DatabaseType::Date,
            'datetime', 'datetime2' => DatabaseType::DateTime,
            'timestamp', 'timestamptz', 'datetimeoffset' => DatabaseType::Timestamp,
            'json', 'jsonb' => DatabaseType::Json,
            'uuid', 'uniqueidentifier' => DatabaseType::Uuid,
            'enum' => DatabaseType::Enum,
            'year' => DatabaseType::Integer,
            'binary', 'varbinary', 'blob', 'bytea', 'raw', 'bit' => DatabaseType::Binary,
            default => DatabaseType::Unknown,
        };
    }
}
