<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use SimbaJirira\SchemaContract\Console\Commands\CheckSchemaContractCommand;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\InvalidBooleanCommandProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\JsonWarningCommandProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\Other\SharedNameProfile as OtherSharedNameProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\Shared\SharedNameProfile;
use SimbaJirira\SchemaContract\Tests\Fixtures\Commands\ValidCommandProfile;

beforeEach(function (): void {
    $this->createCommandCheckTables();
    $this->configureCommandDiscovery();
});

it('registers the schema contract check command', function () {
    expect(collect(Artisan::all())->keys())->toContain('schema-contract:check');
});

it('exits cleanly when all discovered models pass contract checks', function () {
    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            InvalidBooleanCommandProfile::class,
            JsonWarningCommandProfile::class,
        ],
    ]);

    $this->artisan('schema-contract:check', ['model' => ValidCommandProfile::class])
        ->assertSuccessful()
        ->expectsOutputToContain(ValidCommandProfile::class)
        ->expectsOutputToContain('Table: command_check_profiles')
        ->expectsOutputToContain('PASS    active')
        ->expectsOutputToContain('Models inspected: 1')
        ->expectsOutputToContain('Errors: 0');
});

it('returns exit code 1 when contract errors are detected', function () {
    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            ValidCommandProfile::class,
            JsonWarningCommandProfile::class,
        ],
    ]);

    $this->artisan('schema-contract:check', ['model' => InvalidBooleanCommandProfile::class])
        ->assertExitCode(CheckSchemaContractCommand::EXIT_CONTRACT_ERRORS)
        ->expectsOutputToContain('ERROR    active')
        ->expectsOutputToContain('Errors: 1');
});

it('returns exit code 0 when only warnings are present', function () {
    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            ValidCommandProfile::class,
            InvalidBooleanCommandProfile::class,
        ],
    ]);

    $this->artisan('schema-contract:check', ['model' => JsonWarningCommandProfile::class])
        ->assertSuccessful()
        ->expectsOutputToContain('WARNING    payload')
        ->expectsOutputToContain('Warnings: 1')
        ->expectsOutputToContain('Errors: 0');
});

it('reports success when no models are discovered', function () {
    config(['schema-contract.model_paths' => [$this->commandFixturePath().'/Empty']]);

    if (! is_dir($this->commandFixturePath().'/Empty')) {
        mkdir($this->commandFixturePath().'/Empty', 0777, true);
    }

    $this->artisan('schema-contract:check')
        ->assertSuccessful()
        ->expectsOutputToContain('No Eloquent models discovered');
});

it('analyzes a specific model using its short class name', function () {
    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            InvalidBooleanCommandProfile::class,
            JsonWarningCommandProfile::class,
        ],
    ]);

    $this->artisan('schema-contract:check', ['model' => 'ValidCommandProfile'])
        ->assertSuccessful()
        ->expectsOutputToContain(ValidCommandProfile::class)
        ->expectsOutputToContain('Models inspected: 1');
});

it('returns exit code 2 for an invalid model argument', function () {
    $this->artisan('schema-contract:check', ['model' => 'NotARealModel'])
        ->assertExitCode(CheckSchemaContractCommand::EXIT_RUNTIME_FAILURE)
        ->expectsOutputToContain('Unable to resolve Eloquent model');
});

it('returns exit code 2 for ambiguous short model names', function () {
    $this->configureCommandDiscovery([
        $this->commandFixturePath().'/Shared',
        $this->commandFixturePath().'/Other',
    ]);

    $this->artisan('schema-contract:check', ['model' => 'SharedNameProfile'])
        ->assertExitCode(CheckSchemaContractCommand::EXIT_RUNTIME_FAILURE)
        ->expectsOutputToContain('ambiguous');
});

it('discovers and analyzes all configured models by default', function () {
    config([
        'schema-contract.model_paths' => [$this->commandFixturePath()],
        'schema-contract.ignore_models' => [
            InvalidBooleanCommandProfile::class,
            JsonWarningCommandProfile::class,
            SharedNameProfile::class,
            OtherSharedNameProfile::class,
        ],
    ]);

    $this->artisan('schema-contract:check')
        ->assertSuccessful()
        ->expectsOutputToContain('Models inspected: 1');
});
