<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;

readonly class CompatibilityResult
{
    public function __construct(
        public CompatibilityState $state,
        public string $reason,
        public ?Severity $suggestedSeverity = null,
        public ?string $suggestedCast = null,
        public ?DatabaseType $databaseType = null,
        public ?CastType $castType = null,
        public ?int $databaseScale = null,
        public ?int $castScale = null,
    ) {}

    public function isCompatible(): bool
    {
        return $this->state === CompatibilityState::Compatible;
    }
}
