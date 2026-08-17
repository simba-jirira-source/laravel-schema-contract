<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Enums;

enum CastType: string
{
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case Double = 'double';
    case Decimal = 'decimal';
    case String = 'string';
    case Array = 'array';
    case Object = 'object';
    case Collection = 'collection';
    case Date = 'date';
    case DateTime = 'datetime';
    case Timestamp = 'timestamp';
    case Enum = 'enum';
    case Custom = 'custom';
    case Unknown = 'unknown';
}
