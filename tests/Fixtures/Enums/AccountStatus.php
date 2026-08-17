<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
