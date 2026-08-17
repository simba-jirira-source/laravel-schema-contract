<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support;

/**
 * Raw schema column metadata from a database driver before normalization.
 */
readonly class RawColumnMetadata
{
    public function __construct(
        public string $name,
        public string $driverType,
        public bool $nullable = false,
        public ?string $default = null,
        public ?int $length = null,
        public ?int $precision = null,
        public ?int $scale = null,
    ) {}
}
