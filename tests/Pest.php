<?php

declare(strict_types=1);

use SimbaJirira\SchemaContract\Tests\TestCase;

require __DIR__.'/Support/driver-compatibility.php';

/*
|--------------------------------------------------------------------------
| Laravel / Orchestra Testbench
|--------------------------------------------------------------------------
|
| Feature and integration tests require the application container, config,
| database, Artisan, or other Laravel services provided by Tests\TestCase.
|
*/

uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Integration');

/*
|--------------------------------------------------------------------------
| Pure PHP unit tests
|--------------------------------------------------------------------------
|
| Compatibility matrices, DTOs, enums, normalizers, and rule logic that do
| not depend on Laravel run without booting Orchestra Testbench.
|
| Architecture tests scan the codebase statically and also avoid Testbench.
|
*/

uses()->in('Unit');
uses()->in('Architecture');
