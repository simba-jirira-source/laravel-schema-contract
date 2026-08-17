<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Rules;

use SimbaJirira\SchemaContract\Compatibility\TypeCompatibilityMatrix;
use SimbaJirira\SchemaContract\Contracts\ContractRule;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\Support\ViolationFactory;

final class JsonColumnHasCompatibleCastRule implements ContractRule
{
    public const string IDENTIFIER = 'json_column_has_compatible_cast';

    public function __construct(
        private readonly TypeCompatibilityMatrix $matrix = new TypeCompatibilityMatrix,
    ) {}

    public function identifier(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(ModelDefinition $model, ColumnDefinition $column): array
    {
        if ($column->type !== DatabaseType::Json) {
            return [];
        }

        $cast = $model->casts[$column->name] ?? null;
        $result = $this->matrix->compare($column, $cast);

        if ($result->state === CompatibilityState::Compatible) {
            return [];
        }

        if (
            $result->state === CompatibilityState::Uncertain
            && $cast !== null
            && $result->suggestedSeverity === Severity::Info
        ) {
            return [];
        }

        return [
            ViolationFactory::fromCompatibilityResult(self::IDENTIFIER, $model, $column, $cast, $result),
        ];
    }
}
