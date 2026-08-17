<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

use SimbaJirira\SchemaContract\Enums\Severity;

readonly class ContractResult
{
    /**
     * @param  list<ContractViolation>  $violations
     */
    public function __construct(
        public ModelDefinition $model,
        public ?TableDefinition $table,
        public array $violations,
        public int $columnsInspected,
        public int $passedColumns,
        public int $errorCount,
        public int $warningCount,
        public int $infoCount,
    ) {}

    public function modelClass(): string
    {
        return $this->model->modelClass;
    }

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }

    public function hasWarnings(): bool
    {
        return $this->warningCount > 0;
    }

    /**
     * @return list<ContractViolation>
     */
    public function errors(): array
    {
        return $this->violationsBySeverity(Severity::Error);
    }

    /**
     * @return list<ContractViolation>
     */
    public function warnings(): array
    {
        return $this->violationsBySeverity(Severity::Warning);
    }

    /**
     * @return list<ContractViolation>
     */
    public function infos(): array
    {
        return $this->violationsBySeverity(Severity::Info);
    }

    public function passed(): int
    {
        return $this->passedColumns;
    }

    /**
     * @return list<ContractViolation>
     */
    private function violationsBySeverity(Severity $severity): array
    {
        return array_values(array_filter(
            $this->violations,
            fn (ContractViolation $violation): bool => $violation->severity === $severity,
        ));
    }
}
