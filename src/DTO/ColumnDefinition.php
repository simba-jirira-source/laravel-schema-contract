<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

use SimbaJirira\SchemaContract\Enums\DatabaseType;

readonly class ColumnDefinition
{
    public function __construct(
        public string $name,
        public DatabaseType $type,
        public bool $nullable,
        public ?string $default = null,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public ?string $originalDriverType = null,
    ) {}
}
