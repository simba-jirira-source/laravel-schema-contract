<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

readonly class TableDefinition
{
    /**
     * @param  list<ColumnDefinition>  $columns
     */
    public function __construct(
        public string $name,
        public string $connection,
        public array $columns,
    ) {}
}
