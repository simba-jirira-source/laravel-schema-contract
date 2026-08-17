<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('SimbaJirira\SchemaContract')
    ->toUseStrictTypes();

arch('analysis remains independent from console presentation')
    ->expect('SimbaJirira\SchemaContract\Analysis')
    ->not->toUse('SimbaJirira\SchemaContract\Console');

arch('rules remain independent from console presentation')
    ->expect('SimbaJirira\SchemaContract\Rules')
    ->not->toUse('SimbaJirira\SchemaContract\Console');

arch('compatibility checks remain independent from console and rules')
    ->expect('SimbaJirira\SchemaContract\Compatibility')
    ->not->toUse([
        'SimbaJirira\SchemaContract\Console',
        'SimbaJirira\SchemaContract\Rules',
    ]);

arch('contracts do not depend on concrete implementations')
    ->expect('SimbaJirira\SchemaContract\Contracts')
    ->not->toUse([
        'SimbaJirira\SchemaContract\Inspectors',
        'SimbaJirira\SchemaContract\Console',
        'SimbaJirira\SchemaContract\Analysis',
    ]);

arch('domain exceptions remain outside the console layer')
    ->expect('SimbaJirira\SchemaContract\Exceptions')
    ->not->toUse('SimbaJirira\SchemaContract\Console');

arch('dtos are readonly value objects')
    ->expect('SimbaJirira\SchemaContract\DTO')
    ->toBeReadonly();

arch('reflection stays inside model discovery boundaries')
    ->expect('ReflectionClass')
    ->toOnlyBeUsedIn('SimbaJirira\SchemaContract\Discovery\EloquentModelDiscoverer');
