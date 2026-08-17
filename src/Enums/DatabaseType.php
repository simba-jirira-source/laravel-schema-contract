<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Enums;

enum DatabaseType: string
{
    case Boolean = 'boolean';
    case Integer = 'integer';
    case BigInteger = 'big_integer';
    case SmallInteger = 'small_integer';
    case Decimal = 'decimal';
    case Float = 'float';
    case Double = 'double';
    case String = 'string';
    case Text = 'text';
    case Date = 'date';
    case DateTime = 'datetime';
    case Timestamp = 'timestamp';
    case Json = 'json';
    case Uuid = 'uuid';
    case Enum = 'enum';
    case Binary = 'binary';
    case Unknown = 'unknown';
}
