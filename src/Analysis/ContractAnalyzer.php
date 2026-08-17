<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Analysis;

use SimbaJirira\SchemaContract\Contracts\ModelInspector;
use SimbaJirira\SchemaContract\Contracts\SchemaInspector;
use SimbaJirira\SchemaContract\DTO\AnalysisResult;
use SimbaJirira\SchemaContract\DTO\AnalysisSummary;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ContractResult;
use SimbaJirira\SchemaContract\DTO\ContractViolation;
use SimbaJirira\SchemaContract\DTO\ModelDefinition;
use SimbaJirira\SchemaContract\Enums\Severity;
use SimbaJirira\SchemaContract\Exceptions\MissingTableException;
use SimbaJirira\SchemaContract\Inspectors\EloquentModelInspector;
use SimbaJirira\SchemaContract\Inspectors\EloquentSchemaInspector;
use SimbaJirira\SchemaContract\Rules\RuleRegistry;

final class ContractAnalyzer
{
    public const string MISSING_TABLE_RULE = 'schema_table_exists';

    /**
     * @param  list<string>  $ignoreColumns
     */
    public function __construct(
        private readonly ModelInspector $modelInspector = new EloquentModelInspector,
        private readonly SchemaInspector $schemaInspector = new EloquentSchemaInspector,
        private readonly RuleRegistry $ruleRegistry = new RuleRegistry,
        private readonly array $ignoreColumns = [],
    ) {}

    public static function withDefaults(): self
    {
        return new self(
            ruleRegistry: RuleRegistry::withDefaults(),
        );
    }

    /**
     * @param  list<string>  $modelClasses
     */
    public function analyzeModels(array $modelClasses): AnalysisResult
    {
        $modelClasses = $this->sortedUniqueModelClasses($modelClasses);

        $results = [];

        foreach ($modelClasses as $modelClass) {
            $results[] = $this->analyzeModel($modelClass);
        }

        return new AnalysisResult(
            results: $results,
            summary: $this->summarize($results),
        );
    }

    public function analyzeModel(string $modelClass): ContractResult
    {
        $model = $this->modelInspector->inspect($modelClass);

        try {
            $table = $this->schemaInspector->inspect($model);
        } catch (MissingTableException $exception) {
            return $this->missingTableResult($model, $exception);
        }

        $violations = [];
        $columnsInspected = 0;
        $passedColumns = 0;

        $columns = $table->columns;
        usort($columns, fn (ColumnDefinition $left, ColumnDefinition $right): int => strcmp($left->name, $right->name));

        foreach ($columns as $column) {
            if ($this->shouldIgnoreColumn($column->name)) {
                continue;
            }

            $columnsInspected++;
            $columnViolations = $this->ruleRegistry->analyze($model, $column);

            if ($columnViolations === []) {
                $passedColumns++;

                continue;
            }

            array_push($violations, ...$columnViolations);
        }

        $violations = $this->sortViolations($violations);
        $counts = $this->countSeverities($violations);

        return new ContractResult(
            model: $model,
            table: $table,
            violations: $violations,
            columnsInspected: $columnsInspected,
            passedColumns: $passedColumns,
            errorCount: $counts[Severity::Error->value],
            warningCount: $counts[Severity::Warning->value],
            infoCount: $counts[Severity::Info->value],
        );
    }

    private function missingTableResult(ModelDefinition $model, MissingTableException $exception): ContractResult
    {
        $violations = [
            new ContractViolation(
                severity: Severity::Error,
                rule: self::MISSING_TABLE_RULE,
                modelClass: $model->modelClass,
                table: $model->table,
                connection: $model->connection,
                column: '*',
                message: $exception->getMessage(),
            ),
        ];

        return new ContractResult(
            model: $model,
            table: null,
            violations: $violations,
            columnsInspected: 0,
            passedColumns: 0,
            errorCount: 1,
            warningCount: 0,
            infoCount: 0,
        );
    }

    /**
     * @param  list<ContractResult>  $results
     */
    private function summarize(array $results): AnalysisSummary
    {
        $modelsInspected = count($results);
        $columnsInspected = 0;
        $passedColumns = 0;
        $errorCount = 0;
        $warningCount = 0;
        $infoCount = 0;

        foreach ($results as $result) {
            $columnsInspected += $result->columnsInspected;
            $passedColumns += $result->passedColumns;
            $errorCount += $result->errorCount;
            $warningCount += $result->warningCount;
            $infoCount += $result->infoCount;
        }

        return new AnalysisSummary(
            modelsInspected: $modelsInspected,
            columnsInspected: $columnsInspected,
            passedColumns: $passedColumns,
            errorCount: $errorCount,
            warningCount: $warningCount,
            infoCount: $infoCount,
        );
    }

    /**
     * @param  list<string>  $modelClasses
     * @return list<string>
     */
    private function sortedUniqueModelClasses(array $modelClasses): array
    {
        $modelClasses = array_values(array_unique($modelClasses));
        sort($modelClasses);

        return $modelClasses;
    }

    private function shouldIgnoreColumn(string $column): bool
    {
        return in_array($column, $this->ignoreColumns, true);
    }

    /**
     * @param  list<ContractViolation>  $violations
     * @return list<ContractViolation>
     */
    private function sortViolations(array $violations): array
    {
        usort(
            $violations,
            static function (ContractViolation $left, ContractViolation $right): int {
                return [$left->column, $left->rule, $left->message] <=> [$right->column, $right->rule, $right->message];
            },
        );

        return $violations;
    }

    /**
     * @param  list<ContractViolation>  $violations
     * @return array{error: int, warning: int, info: int}
     */
    private function countSeverities(array $violations): array
    {
        $counts = [
            Severity::Error->value => 0,
            Severity::Warning->value => 0,
            Severity::Info->value => 0,
        ];

        foreach ($violations as $violation) {
            $counts[$violation->severity->value]++;
        }

        return $counts;
    }
}
