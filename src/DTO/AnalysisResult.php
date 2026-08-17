<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

use SimbaJirira\SchemaContract\Enums\Severity;

readonly class AnalysisResult
{
    /**
     * @param  list<ContractResult>  $results
     */
    public function __construct(
        public array $results,
        public AnalysisSummary $summary,
    ) {}

    public function hasErrors(): bool
    {
        return $this->summary->hasErrors();
    }

    public function hasWarnings(): bool
    {
        return $this->summary->hasWarnings();
    }

    /**
     * @return list<ContractViolation>
     */
    public function errors(): array
    {
        return $this->collectViolations(Severity::Error);
    }

    /**
     * @return list<ContractViolation>
     */
    public function warnings(): array
    {
        return $this->collectViolations(Severity::Warning);
    }

    /**
     * @return list<ContractViolation>
     */
    public function infos(): array
    {
        return $this->collectViolations(Severity::Info);
    }

    public function passed(): int
    {
        return $this->summary->passedColumns;
    }

    /**
     * @return list<ContractViolation>
     */
    private function collectViolations(Severity $severity): array
    {
        $violations = [];

        foreach ($this->results as $result) {
            array_push($violations, ...match ($severity) {
                Severity::Error => $result->errors(),
                Severity::Warning => $result->warnings(),
                Severity::Info => $result->infos(),
            });
        }

        return $violations;
    }
}
