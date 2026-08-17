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

it('reads ignored columns from application configuration', function () {
    config([
        'schema-contract.ignore_columns' => [
            'profiles' => ['secret'],
        ],
    ]);

    expect(IgnoreColumnMatcher::fromConfig()->shouldIgnore('profiles', 'secret'))->toBeTrue()
        ->and(IgnoreColumnMatcher::fromConfig()->shouldIgnore('profiles', 'name'))->toBeFalse();
});

it('returns no ignored columns when configuration is empty or invalid', function () {
    config(['schema-contract.ignore_columns' => []]);

    expect(IgnoreColumnMatcher::fromConfig()->ignoredColumnsFor('users'))->toBe([]);

    config(['schema-contract.ignore_columns' => 'invalid']);

    expect(IgnoreColumnMatcher::fromConfig()->shouldIgnore('users', 'password'))->toBeFalse();
});

it('deduplicates ignored columns for a table', function () {
    $matcher = IgnoreColumnMatcher::fromConfigured([
        'users' => ['password', 'password', 'remember_token'],
    ]);

    expect($matcher->ignoredColumnsFor('users'))->toBe(['password', 'remember_token']);
});
