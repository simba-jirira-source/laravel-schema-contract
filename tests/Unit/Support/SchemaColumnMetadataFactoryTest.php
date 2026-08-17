<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Support\SchemaColumnMetadataFactory;

it('builds raw metadata from schema column arrays', function () {
    $metadata = (new SchemaColumnMetadataFactory)->make([
        'name' => 'price',
        'type' => 'decimal(10,2)',
        'type_name' => 'decimal',
        'nullable' => true,
        'default' => '0.00',
    ]);

    expect($metadata->name)->toBe('price')
        ->and($metadata->driverType)->toBe('decimal(10,2)')
        ->and($metadata->nullable)->toBeTrue()
        ->and($metadata->default)->toBe('0.00');
});

it('falls back to type_name and unknown for missing driver metadata', function () {
    $fromTypeName = (new SchemaColumnMetadataFactory)->make([
        'name' => 'meta',
        'type' => '',
        'type_name' => 'json',
        'nullable' => false,
    ]);

    $unknown = (new SchemaColumnMetadataFactory)->make([
        'name' => 'meta',
        'type' => '',
        'type_name' => '',
        'nullable' => false,
    ]);

    expect($fromTypeName->driverType)->toBe('json')
        ->and($unknown->driverType)->toBe('unknown');
});
