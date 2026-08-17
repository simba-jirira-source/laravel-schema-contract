<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class StandardModel extends Model
{
    protected $table = 'standard_models';

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
