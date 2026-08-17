<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Commands;

use Illuminate\Database\Eloquent\Model;

class InvalidBooleanCommandProfile extends Model
{
    protected $table = 'command_check_profiles';

    protected function casts(): array
    {
        return [
            'active' => 'integer',
            'payload' => 'array',
        ];
    }
}
