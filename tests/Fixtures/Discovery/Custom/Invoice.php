<?php

declare(strict_types=1);

namespace SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Custom;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'discovery_invoices';
}
