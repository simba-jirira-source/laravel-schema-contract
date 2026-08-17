<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Analysis;

use Illuminate\Database\Eloquent\Model;

class MissingTableAnalyzerProfile extends Model
{
    protected $table = 'analyzer_missing_profiles';
}
