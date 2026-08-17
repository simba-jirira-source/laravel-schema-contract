<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Standard;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'discovery_articles';
}
