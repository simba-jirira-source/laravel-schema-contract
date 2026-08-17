<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Analysis;

use Illuminate\Database\Eloquent\Model;

class DecimalScaleMismatchAnalyzerProfile extends Model
{
    protected $table = 'analyzer_profiles';

    protected function casts(): array
    {
        return [
            'bonus' => 'decimal:2',
        ];
    }
}
