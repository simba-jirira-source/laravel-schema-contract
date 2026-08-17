<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Support\IgnoreColumnMatcher;

it('matches ignored columns for the configured table only', function () {
    $matcher = IgnoreColumnMatcher::fromConfigured([
        'users' => ['password', 'remember_token'],
        'posts' => ['legacy_flag'],
    ]);

    expect($matcher->shouldIgnore('users', 'password'))->toBeTrue()
        ->and($matcher->shouldIgnore('users', 'remember_token'))->toBeTrue()
        ->and($matcher->shouldIgnore('users', 'email'))->toBeFalse()
        ->and($matcher->shouldIgnore('posts', 'legacy_flag'))->toBeTrue()
        ->and($matcher->shouldIgnore('posts', 'password'))->toBeFalse();
});

it('ignores invalid configuration entries safely', function () {
    $matcher = IgnoreColumnMatcher::fromConfigured([
        '' => ['password'],
        'users' => 'password',
        'posts' => ['', 123, 'legacy_flag'],
        0 => ['ignored_by_numeric_key'],
    ]);

    expect($matcher->shouldIgnore('users', 'password'))->toBeFalse()
        ->and($matcher->shouldIgnore('posts', 'legacy_flag'))->toBeTrue()
        ->and($matcher->ignoredColumnsFor('posts'))->toBe(['legacy_flag']);
});

it('deduplicates ignored columns for a table', function () {
    $matcher = IgnoreColumnMatcher::fromConfigured([
        'users' => ['password', 'password', 'remember_token'],
    ]);

    expect($matcher->ignoredColumnsFor('users'))->toBe(['password', 'remember_token']);
});
