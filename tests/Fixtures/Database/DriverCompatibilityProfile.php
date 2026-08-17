<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Database;

use Illuminate\Database\Eloquent\Model;

class DriverCompatibilityProfile extends Model
{
    protected $table = 'driver_compatibility_profiles';

    protected $connection = 'driver_testing';

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'small_count' => 'integer',
            'count' => 'integer',
            'big_count' => 'integer',
            'amount' => 'decimal:2',
            'ratio' => 'float',
            'precise_ratio' => 'double',
            'code' => 'string',
            'bio' => 'string',
            'payload' => 'array',
            'external_id' => 'string',
            'starts_on' => 'date',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'status' => 'string',
        ];
    }
}
