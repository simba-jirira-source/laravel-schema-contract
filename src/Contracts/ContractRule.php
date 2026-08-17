<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Contracts;

use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ContractViolation;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;

interface ContractRule
{
    public function identifier(): string;

    /**
     * @return list<ContractViolation>
     */
    public function analyze(ModelDefinition $model, ColumnDefinition $column): array;
}
