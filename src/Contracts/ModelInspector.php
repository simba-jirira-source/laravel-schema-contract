<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Contracts;

use SimbaJirira\SchemaContract\DTO\ModelDefinition;

interface ModelInspector
{
    public function inspect(string $modelClass): ModelDefinition;
}
