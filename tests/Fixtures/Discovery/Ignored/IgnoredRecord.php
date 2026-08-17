<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Ignored;

use Illuminate\Database\Eloquent\Model;

class IgnoredRecord extends Model
{
    protected $table = 'discovery_ignored_records';
}
