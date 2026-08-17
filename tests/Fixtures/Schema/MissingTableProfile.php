<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Schema;

use Illuminate\Database\Eloquent\Model;

class MissingTableProfile extends Model
{
    protected $table = 'schema_inspection_missing_profiles';
}
