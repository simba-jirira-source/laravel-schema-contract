<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Contracts;

interface ModelDiscoverer
{
    /**
     * Discover concrete Eloquent model classes from configured paths.
     *
     * @return list<string>
     */
    public function discover(): array;
}
