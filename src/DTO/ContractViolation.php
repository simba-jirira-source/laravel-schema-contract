<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;

readonly class ContractViolation
{
    public function __construct(
        public Severity $severity,
        public string $modelClass,
        public string $column,
        public string $message,
        public ?string $suggestedCast = null,
        public ?DatabaseType $databaseType = null,
        public ?CastType $castType = null,
        public ?int $databasePrecision = null,
        public ?int $databaseScale = null,
        public ?int $castScale = null,
    ) {}
}
