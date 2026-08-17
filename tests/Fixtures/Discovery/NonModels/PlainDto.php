<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\NonModels;

final class PlainDto
{
    public function __construct(public string $value) {}
}
