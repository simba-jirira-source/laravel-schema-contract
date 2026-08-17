<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

use SimbaJirira\SchemaContract\Support\Database\DriverColumnMetadataEnricher;

final class SchemaColumnMetadataFactory
{
    public function __construct(
        private readonly ColumnTypeParser $typeParser = new ColumnTypeParser,
        private readonly DriverColumnMetadataEnricher $driverEnricher = new DriverColumnMetadataEnricher,
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
    public function make(array $column, ?string $driver = null): RawColumnMetadata
    {
        $driverType = trim($column['type']);

        if ($driverType === '') {
            $driverType = trim($column['type_name']);
        }

        if ($driverType === '') {
            $driverType = 'unknown';
        }

        $parsed = $this->typeParser->parse($driverType);

        $metadata = new RawColumnMetadata(
            name: $column['name'],
            driverType: $driverType,
            nullable: (bool) $column['nullable'],
            default: $this->stringifyDefault($column['default'] ?? null),
            length: $parsed['length'],
            precision: $parsed['precision'],
            scale: $parsed['scale'],
        );

        if ($driver === null) {
            return $metadata;
        }

        return $this->driverEnricher->enrich($driver, $column, $metadata);
    }

    private function stringifyDefault(mixed $default): ?string
    {
        if ($default === null) {
            return null;
        }

        if (is_string($default)) {
            return $default;
        }

        if (is_bool($default)) {
            return $default ? '1' : '0';
        }

        if (is_int($default) || is_float($default)) {
            return (string) $default;
        }

        return json_encode($default) ?: null;
    }
}
