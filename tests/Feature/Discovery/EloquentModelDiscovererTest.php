<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Discovery\EloquentModelDiscoverer;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\AbstractModels\AbstractRecord;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Custom\Invoice;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Ignored\IgnoredRecord;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Nested\Admin\Operator;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\NonModels\PlainDto;
use SimbaJirira\SchemaContract\Tests\Fixtures\Discovery\Standard\Article;

beforeEach(function (): void {
    $this->discoverer = new EloquentModelDiscoverer;
    $this->discoveryRoot = __DIR__.'/../../Fixtures/Discovery';
});

it('discovers concrete models from a standard model path', function () {
    $models = $this->discoverer->discoverInPaths([
        $this->discoveryRoot.'/Standard',
    ]);

    expect($models)->toBe([
        Article::class,
    ]);
});

it('discovers models from nested namespaces', function () {
    $models = $this->discoverer->discoverInPaths([
        $this->discoveryRoot.'/Nested',
    ]);

    expect($models)->toBe([
        Operator::class,
    ]);
});

it('discovers models from custom configured paths', function () {
    config([
        'schema-contract.model_paths' => [
            $this->discoveryRoot.'/Custom',
        ],
    ]);

    expect($this->discoverer->discover())->toBe([
        Invoice::class,
    ]);
});

it('defaults to the application models path when configured paths are empty', function () {
    config(['schema-contract.model_paths' => []]);

    expect($this->discoverer->discover())->toBe(
        $this->discoverer->discoverInPaths([app_path('Models')]),
    );
});

it('skips abstract classes traits interfaces and unrelated classes', function () {
    $models = $this->discoverer->discoverInPaths([
        $this->discoveryRoot.'/AbstractModels',
        $this->discoveryRoot.'/NonModels',
        $this->discoveryRoot.'/Support',
    ]);

    expect($models)->toBe([])
        ->and(class_exists(AbstractRecord::class))->toBeTrue()
        ->and(class_exists(PlainDto::class))->toBeTrue();
});

it('prevents duplicate model classes when paths overlap', function () {
    $models = $this->discoverer->discoverInPaths([
        $this->discoveryRoot.'/Standard',
        $this->discoveryRoot.'/Standard',
    ]);

    expect($models)->toBe([
        Article::class,
    ]);
});

it('skips ignored models from configuration', function () {
    config([
        'schema-contract.model_paths' => [
            $this->discoveryRoot.'/Ignored',
            $this->discoveryRoot.'/Standard',
        ],
        'schema-contract.ignore_models' => [
            IgnoredRecord::class,
        ],
    ]);

    expect($this->discoverer->discover())->toBe([
        Article::class,
    ]);
});

it('returns only class names without performing model inspection', function () {
    $models = $this->discoverer->discoverInPaths([
        $this->discoveryRoot.'/Standard',
    ]);

    expect($models[0])->toBeString()
        ->and($models[0])->toBe(Article::class);
});

it('ignores missing model paths without failing', function () {
    $models = $this->discoverer->discoverInPaths([
        $this->discoveryRoot.'/Missing',
        $this->discoveryRoot.'/Standard',
    ]);

    expect($models)->toBe([
        Article::class,
    ]);
});
