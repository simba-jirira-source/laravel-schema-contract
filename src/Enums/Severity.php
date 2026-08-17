<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Enums;

enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';
}
