<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Analysis;

use Illuminate\Database\Eloquent\Model;

class ValidAnalyzerProfile extends Model
{
    protected $table = 'analyzer_profiles';

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
