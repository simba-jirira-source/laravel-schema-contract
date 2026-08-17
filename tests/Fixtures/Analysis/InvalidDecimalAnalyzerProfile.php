<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Analysis;

use Illuminate\Database\Eloquent\Model;

class InvalidDecimalAnalyzerProfile extends Model
{
    protected $table = 'analyzer_profiles';

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'payload' => 'array',
        ];
    }
}
