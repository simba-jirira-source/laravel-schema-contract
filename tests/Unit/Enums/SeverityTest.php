<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\Severity;

it('defines contract violation severities', function (Severity $severity, string $value) {
    expect($severity->value)->toBe($value);
})->with([
    [Severity::Error, 'error'],
    [Severity::Warning, 'warning'],
    [Severity::Info, 'info'],
]);

it('orders severities for blocking analysis semantics', function () {
    expect(Severity::Error)->not->toBe(Severity::Warning);
    expect(Severity::Warning)->not->toBe(Severity::Info);
});
