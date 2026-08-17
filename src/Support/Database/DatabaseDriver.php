<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Support\Database;

enum DatabaseDriver: string
{
    case Sqlite = 'sqlite';
    case Mysql = 'mysql';
    case Mariadb = 'mariadb';
    case Pgsql = 'pgsql';
    case Unknown = 'unknown';

    public static function fromDriverName(string $driver): self
    {
        return self::tryFrom($driver) ?? self::Unknown;
    }

    public function label(): string
    {
        return match ($this) {
            self::Sqlite => 'SQLite',
            self::Mysql => 'MySQL',
            self::Mariadb => 'MariaDB',
            self::Pgsql => 'PostgreSQL',
            self::Unknown => 'Unknown',
        };
    }
}
