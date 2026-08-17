<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

readonly class ModelDefinition
{
    /**
     * @param  array<string, CastDefinition>  $casts
     */
    public function __construct(
        public string $modelClass,
        public string $connection,
        public string $table,
        public string $primaryKey,
        public array $casts = [],
    ) {}
}
