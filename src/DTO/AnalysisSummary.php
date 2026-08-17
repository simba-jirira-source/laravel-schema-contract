<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\DTO;

readonly class AnalysisSummary
{
    public function __construct(
        public int $modelsInspected,
        public int $columnsInspected,
        public int $passedColumns,
        public int $errorCount,
        public int $warningCount,
        public int $infoCount,
    ) {}

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }

    public function hasWarnings(): bool
    {
        return $this->warningCount > 0;
    }
}
