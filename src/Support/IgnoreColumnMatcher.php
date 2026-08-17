<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

final class IgnoreColumnMatcher
{
    /**
     * @param  array<string, list<string>>  $ignoreColumnsByTable
     */
    public function __construct(
        private readonly array $ignoreColumnsByTable = [],
    ) {}

    public static function fromConfig(): self
    {
        /** @var mixed $configured */
        $configured = config('schema-contract.ignore_columns', []);

        return self::fromConfigured(is_array($configured) ? $configured : []);
    }

    /**
     * @param  array<string|int, mixed>  $configured
     */
    public static function fromConfigured(array $configured): self
    {
        /** @var array<string, list<string>> $ignoreColumnsByTable */
        $ignoreColumnsByTable = [];

        foreach ($configured as $table => $columns) {
            if (! is_string($table) || $table === '') {
                continue;
            }

            if (! is_array($columns)) {
                continue;
            }

            $normalized = [];

            foreach ($columns as $column) {
                if (is_string($column) && $column !== '') {
                    $normalized[] = $column;
                }
            }

            if ($normalized === []) {
                continue;
            }

            $ignoreColumnsByTable[$table] = array_values(array_unique($normalized));
        }

        return new self($ignoreColumnsByTable);
    }

    public function shouldIgnore(string $table, string $column): bool
    {
        return in_array($column, $this->ignoreColumnsByTable[$table] ?? [], true);
    }

    /**
     * @return list<string>
     */
    public function ignoredColumnsFor(string $table): array
    {
        return $this->ignoreColumnsByTable[$table] ?? [];
    }
}
