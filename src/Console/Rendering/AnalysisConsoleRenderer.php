<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Console\Rendering;

use Illuminate\Console\OutputStyle;
use SimbaJirira\SchemaContract\DTO\AnalysisSummary;
use SimbaJirira\SchemaContract\DTO\ColumnDefinition;
use SimbaJirira\SchemaContract\DTO\ContractResult;
use SimbaJirira\SchemaContract\DTO\ContractViolation;
use SimbaJirira\SchemaContract\Enums\DatabaseType;

final class AnalysisConsoleRenderer
{
    /**
     * @param  list<string>  $ignoreColumns
     */
    public function render(OutputStyle $output, ContractResult $result, array $ignoreColumns = []): void
    {
        $output->writeln($result->modelClass());
        $output->writeln('Table: '.$result->model->table);
        $output->newLine();

        if ($result->table === null) {
            foreach ($result->violations as $violation) {
                $this->renderViolationLine($output, $violation);
            }

            $output->newLine();

            return;
        }

        $violationsByColumn = $this->groupViolationsByColumn($result->violations);
        $columns = $result->table->columns;
        usort($columns, fn (ColumnDefinition $left, ColumnDefinition $right): int => strcmp($left->name, $right->name));

        foreach ($columns as $column) {
            if (in_array($column->name, $ignoreColumns, true)) {
                continue;
            }

            $columnViolations = $violationsByColumn[$column->name] ?? [];

            if ($columnViolations === []) {
                $this->renderPassedColumn($output, $result, $column);

                continue;
            }

            foreach ($columnViolations as $violation) {
                $this->renderViolationLine($output, $violation, $column);
            }
        }

        $output->newLine();
    }

    public function renderSummary(OutputStyle $output, AnalysisSummary $summary): void
    {
        $output->writeln('Models inspected: '.$summary->modelsInspected);
        $output->writeln('Columns inspected: '.$summary->columnsInspected);
        $output->writeln('Errors: '.$summary->errorCount);
        $output->writeln('Warnings: '.$summary->warningCount);
        $output->writeln('Passed: '.$summary->passedColumns);
    }

    private function renderPassedColumn(OutputStyle $output, ContractResult $result, ColumnDefinition $column): void
    {
        $output->writeln('PASS    '.$column->name);
        $output->writeln('        database: '.$this->formatDatabaseType($column));
        $output->writeln('        cast: '.$this->formatCast($result, $column->name));
    }

    private function renderViolationLine(
        OutputStyle $output,
        ContractViolation $violation,
        ?ColumnDefinition $column = null,
    ): void {
        $label = strtoupper($violation->severity->value);
        $columnName = $violation->column === '*' ? 'table' : $violation->column;

        $output->writeln($label.'    '.$columnName);

        if ($column !== null) {
            $output->writeln('        database: '.$this->formatDatabaseType($column));
            $output->writeln('        cast: '.$this->formatCastExpression($violation->modelCast));
        } elseif ($violation->databaseType !== null) {
            $output->writeln('        database: '.$violation->databaseType->value);
            $output->writeln('        cast: '.$this->formatCastExpression($violation->modelCast));
        }

        if ($violation->suggestedCast !== null) {
            $output->writeln('        suggested: '.$violation->suggestedCast);
        }
    }

    private function formatCast(ContractResult $result, string $column): string
    {
        return $this->formatCastExpression($result->model->casts[$column]->originalExpression ?? null);
    }

    private function formatCastExpression(?string $expression): string
    {
        return $expression ?? 'none';
    }

    private function formatDatabaseType(ColumnDefinition $column): string
    {
        if ($column->type === DatabaseType::Decimal && ($column->precision !== null || $column->scale !== null)) {
            $precision = $column->precision ?? '?';
            $scale = $column->scale ?? '?';

            return "decimal({$precision},{$scale})";
        }

        if ($column->length !== null) {
            return $column->type->value.'('.$column->length.')';
        }

        if ($column->originalDriverType !== null && $column->originalDriverType !== '') {
            return strtolower($column->originalDriverType);
        }

        return $column->type->value;
    }

    /**
     * @param  list<ContractViolation>  $violations
     * @return array<string, list<ContractViolation>>
     */
    private function groupViolationsByColumn(array $violations): array
    {
        $grouped = [];

        foreach ($violations as $violation) {
            $grouped[$violation->column][] = $violation;
        }

        return $grouped;
    }
}
