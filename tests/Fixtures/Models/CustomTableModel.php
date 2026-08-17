<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class CustomTableModel extends Model
{
    protected $table = 'legacy_users';
}
