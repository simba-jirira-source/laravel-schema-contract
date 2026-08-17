<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Enums\CompatibilityState;

it('defines compatibility states for matrix evaluation', function () {
    expect(CompatibilityState::cases())->toHaveCount(3)
        ->and(CompatibilityState::Compatible->value)->toBe('compatible')
        ->and(CompatibilityState::Incompatible->value)->toBe('incompatible')
        ->and(CompatibilityState::Uncertain->value)->toBe('uncertain');
});
