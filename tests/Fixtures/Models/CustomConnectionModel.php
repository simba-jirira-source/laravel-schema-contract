<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class CustomConnectionModel extends Model
{
    protected $table = 'remote_records';

    protected $connection = 'analytics';
}
