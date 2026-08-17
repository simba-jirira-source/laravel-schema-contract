<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Enums;

enum CompatibilityState: string
{
    case Compatible = 'compatible';
    case Incompatible = 'incompatible';
    case Uncertain = 'uncertain';
}
