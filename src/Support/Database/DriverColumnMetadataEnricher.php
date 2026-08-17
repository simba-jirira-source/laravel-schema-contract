<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support\Database;

use SimbaJirira\SchemaContract\Support\ColumnTypeParser;
use SimbaJirira\SchemaContract\Support\RawColumnMetadata;

final class DriverColumnMetadataEnricher
{
    public function __construct(
        private readonly ColumnTypeParser $typeParser = new ColumnTypeParser,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     type: string,
     *     type_name: string,
     *     nullable: bool,
     *     default?: mixed,
     *     ...
     * }  $column
     */
    public function enrich(string $driver, array $column, RawColumnMetadata $metadata): RawColumnMetadata
    {
        return match (DatabaseDriver::fromDriverName($driver)) {
            DatabaseDriver::Sqlite => $this->forSqlite($column, $metadata),
            DatabaseDriver::Mysql, DatabaseDriver::Mariadb => $this->forMysql($column, $metadata),
            DatabaseDriver::Pgsql => $this->forPostgres($column, $metadata),
            DatabaseDriver::Unknown => $metadata,
        };
    }

    /**
     * @param  array{name: string, type: string, type_name: string, nullable: bool, default?: mixed}  $column
     */
    private function forSqlite(array $column, RawColumnMetadata $metadata): RawColumnMetadata
    {
        $driverType = $this->preferredDriverType($column);

        if ($driverType === '') {
            return $metadata;
        }

        $parsed = $this->typeParser->parse($driverType);

        return new RawColumnMetadata(
            name: $metadata->name,
            driverType: $driverType,
            nullable: $metadata->nullable,
            default: $metadata->default,
            length: $metadata->length ?? $parsed['length'],
            precision: $metadata->precision ?? $parsed['precision'],
            scale: $metadata->scale ?? $parsed['scale'],
        );
    }

    /**
     * @param  array{name: string, type: string, type_name: string, nullable: bool, default?: mixed}  $column
     */
    private function forMysql(array $column, RawColumnMetadata $metadata): RawColumnMetadata
    {
        $driverType = $this->preferredDriverType($column);

        if ($driverType === '') {
            return $metadata;
        }

        if ($column['type_name'] === 'enum') {
            $driverType = trim($column['type']);
        }

        $parsed = $this->typeParser->parse($driverType);

        return new RawColumnMetadata(
            name: $metadata->name,
            driverType: $driverType,
            nullable: $metadata->nullable,
            default: $metadata->default,
            length: $metadata->length ?? $parsed['length'],
            precision: $metadata->precision ?? $parsed['precision'],
            scale: $metadata->scale ?? $parsed['scale'],
        );
    }

    /**
     * @param  array{name: string, type: string, type_name: string, nullable: bool, default?: mixed}  $column
     */
    private function forPostgres(array $column, RawColumnMetadata $metadata): RawColumnMetadata
    {
        $driverType = $this->preferredDriverType($column);

        if ($driverType === '') {
            return $metadata;
        }

        $parsed = $this->typeParser->parse($driverType);

        if (
            ! str_contains($driverType, '(')
            && in_array($column['type_name'], ['int2', 'int4', 'int8', 'bool', 'jsonb', 'uuid'], true)
        ) {
            $driverType = $column['type_name'];
        }

        return new RawColumnMetadata(
            name: $metadata->name,
            driverType: $driverType,
            nullable: $metadata->nullable,
            default: $metadata->default,
            length: $metadata->length ?? $parsed['length'],
            precision: $metadata->precision ?? $parsed['precision'],
            scale: $metadata->scale ?? $parsed['scale'],
        );
    }

    /**
     * @param  array{type: string, type_name: string}  $column
     */
    private function preferredDriverType(array $column): string
    {
        $type = trim($column['type']);

        if ($type !== '') {
            return $type;
        }

        return trim($column['type_name']);
    }
}
