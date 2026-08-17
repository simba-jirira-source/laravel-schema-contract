<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Exceptions;

use RuntimeException;

final class MissingTableException extends RuntimeException
{
    public function __construct(
        public readonly string $modelClass,
        public readonly string $connection,
        public readonly string $table,
    ) {
        parent::__construct("Table [{$table}] does not exist on connection [{$connection}] for model [{$modelClass}].");
    }
}
