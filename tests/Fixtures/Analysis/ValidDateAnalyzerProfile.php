<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Analysis;

use Illuminate\Database\Eloquent\Model;

class ValidDateAnalyzerProfile extends Model
{
    protected $table = 'analyzer_profiles';

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'published_at' => 'datetime',
        ];
    }
}
