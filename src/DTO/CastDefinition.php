<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

use SimbaJirira\SchemaContract\Enums\CastType;

readonly class CastDefinition
{
    public function __construct(
        public string $column,
        public CastType $type,
        public ?string $originalExpression = null,
        public ?int $scale = null,
        public ?string $customClass = null,
    ) {}
}
