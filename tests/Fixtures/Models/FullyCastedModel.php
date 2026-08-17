<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use SimbaJirira\SchemaContract\Tests\Fixtures\Casts\PreferencesCast;
use SimbaJirira\SchemaContract\Tests\Fixtures\Enums\AccountStatus;

class FullyCastedModel extends Model
{
    protected $table = 'fully_casted_models';

    protected function casts(): array
    {
        return [
            'active' => 'bool',
            'quantity' => 'int',
            'ratio' => 'real',
            'weight' => 'double',
            'price' => 'decimal:2',
            'label' => 'string',
            'tags' => 'array',
            'payload' => 'json',
            'meta' => 'object',
            'items' => 'collection',
            'starts_on' => 'date',
            'published_at' => 'datetime',
            'archived_on' => 'immutable_date',
            'locked_at' => 'immutable_datetime',
            'seen_at' => 'timestamp',
            'status' => AccountStatus::class,
            'preferences' => PreferencesCast::class,
        ];
    }
}
