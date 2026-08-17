<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\AbstractModels;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractRecord extends Model
{
    protected $table = 'discovery_abstract_records';
}
