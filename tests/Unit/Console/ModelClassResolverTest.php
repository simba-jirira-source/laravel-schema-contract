<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Console\Exceptions\AmbiguousModelClassException;
use SimbaJirira\SchemaContract\Console\Exceptions\UnresolvableModelClassException;
use SimbaJirira\SchemaContract\Console\Support\ModelClassResolver;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\ValidCommandProfile;

it('resolves fully qualified model classes', function () {
    $resolved = (new ModelClassResolver)->resolve(
        ValidCommandProfile::class,
        [],
    );

    expect($resolved)->toBe(ValidCommandProfile::class);
});

it('resolves short model names against discovered models', function () {
    $resolved = (new ModelClassResolver)->resolve(
        'ValidCommandProfile',
        [ValidCommandProfile::class],
    );

    expect($resolved)->toBe(ValidCommandProfile::class);
});

it('throws when a short model name is ambiguous', function () {
    (new ModelClassResolver)->resolve('SharedNameProfile', [
        'App\\One\\SharedNameProfile',
        'App\\Two\\SharedNameProfile',
    ]);
})->throws(AmbiguousModelClassException::class);

it('throws when a model cannot be resolved', function () {
    (new ModelClassResolver)->resolve('MissingModel', []);
})->throws(UnresolvableModelClassException::class);
