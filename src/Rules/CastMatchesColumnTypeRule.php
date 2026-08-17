<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Rules;

use SimbaJirira\SchemaContract\Compatibility\TypeCompatibilityMatrix;
use SimbaJirira\SchemaContract\Contracts\ContractRule;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Enums\CastType;
use SimbaJirira\SchemaContract\Enums\CompatibilityState;
use SimbaJirira\SchemaContract\Enums\DatabaseType;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Rules\Support\ViolationFactory;

final class CastMatchesColumnTypeRule implements ContractRule
{
    public const string IDENTIFIER = 'cast_matches_column_type';

    public function __construct(
        private readonly TypeCompatibilityMatrix $matrix = new TypeCompatibilityMatrix,
    ) {}

    public function identifier(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(ModelDefinition $model, ColumnDefinition $column): array
    {
        if ($this->isHandledBySpecializedRule($column)) {
            return [];
        }

        $cast = $model->casts[$column->name] ?? null;

        if ($column->type === DatabaseType::Decimal && $cast?->type === CastType::Decimal) {
            return [];
        }

        $result = $this->matrix->compare($column, $cast);

        if ($result->state === CompatibilityState::Compatible) {
            return [];
        }

        if (
            $result->state === CompatibilityState::Uncertain
            && $result->suggestedSeverity === Severity::Info
        ) {
            return [];
        }

        return [
            ViolationFactory::fromCompatibilityResult(self::IDENTIFIER, $model, $column, $cast, $result),
        ];
    }

    private function isHandledBySpecializedRule(ColumnDefinition $column): bool
    {
        return $column->type === DatabaseType::Json
            || in_array($column->type, [DatabaseType::Date, DatabaseType::DateTime, DatabaseType::Timestamp], true);
    }
}
