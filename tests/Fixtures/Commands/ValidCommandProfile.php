<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Commands;

use Illuminate\Database\Eloquent\Model;

class ValidCommandProfile extends Model
{
    protected $table = 'command_check_profiles';

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'price' => 'decimal:2',
            'payload' => 'array',
            'starts_on' => 'date',
            'published_at' => 'datetime',
        ];
    }
}
