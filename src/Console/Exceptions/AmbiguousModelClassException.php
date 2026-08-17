<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Console\Exceptions;

use RuntimeException;

final class AmbiguousModelClassException extends RuntimeException
{
    /**
     * @param  list<string>  $matches
     */
    public function __construct(
        public readonly string $input,
        public readonly array $matches,
    ) {
        parent::__construct(sprintf(
            'Model short name [%s] is ambiguous. Matches: %s',
            $input,
            implode(', ', $matches),
        ));
    }
}
