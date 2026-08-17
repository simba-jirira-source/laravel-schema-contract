<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Schema;

use Illuminate\Database\Eloquent\Model;

class RemoteProfile extends Model
{
    protected $connection = 'analytics';

    protected $table = 'remote_profiles';
}
