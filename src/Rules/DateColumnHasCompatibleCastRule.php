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

final class DateColumnHasCompatibleCastRule implements ContractRule
{
    public const string IDENTIFIER = 'date_column_has_compatible_cast';

    /** @var list<string> */
    private const array STANDARD_TIMESTAMP_COLUMNS = [
        'created_at',
        'updated_at',
    ];

    public function __construct(
        private readonly TypeCompatibilityMatrix $matrix = new TypeCompatibilityMatrix,
    ) {}

    public function identifier(): string
    {
        return self::IDENTIFIER;
    }

    public function analyze(ModelDefinition $model, ColumnDefinition $column): array
    {
        if (! $this->isDateTimeColumn($column)) {
            return [];
        }

        if (in_array($column->name, self::STANDARD_TIMESTAMP_COLUMNS, true)) {
            return [];
        }

        $cast = $model->casts[$column->name] ?? null;
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

    private function isDateTimeColumn(ColumnDefinition $column): bool
    {
        return in_array($column->type, [
            DatabaseType::Date,
            DatabaseType::DateTime,
            DatabaseType::Timestamp,
        ], true);
    }
}
