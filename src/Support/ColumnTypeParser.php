<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

final class ColumnTypeParser
{
    /**
     * @return array{base: string, length: ?int, precision: ?int, scale: ?int}
     */
    public function parse(string $driverType): array
    {
        $normalized = strtolower(trim($driverType));
        $normalized = (string) preg_replace('/\s+(unsigned|zerofill)\b/', '', $normalized);
        $normalized = $this->stripTimeZoneQualifiers($normalized);

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

    private function stripTimeZoneQualifiers(string $driverType): string
    {
        $driverType = (string) preg_replace('/\s+without time zone$/', '', $driverType);
        $driverType = (string) preg_replace('/\s+with time zone$/', '', $driverType);

        return trim($driverType);
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
}
