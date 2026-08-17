<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Contracts;

use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\DTO\TableDefinition;

interface SchemaInspector
{
    public function inspect(ModelDefinition $model): TableDefinition;
}
