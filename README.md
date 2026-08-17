<div align="center">
    <h1>Laravel Schema Contract</h1>
    <p>Detect inconsistencies between Laravel database schema metadata and Eloquent model casts.</p>
</div>

<p align="center">
    <a href="https://packagist.org/packages/simba-jirira-source/laravel-schema-contract"><img src="https://img.shields.io/packagist/v/simba-jirira-source/laravel-schema-contract.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/simba-jirira-source/laravel-schema-contract"><img src="https://img.shields.io/packagist/php-v/simba-jirira-source/laravel-schema-contract.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/simba-jirira-source/laravel-schema-contract"><img src="https://badge.laravel.cloud/badge/simba-jirira-source/laravel-schema-contract?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/simba-jirira-source/laravel-schema-contract/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/simba-jirira-source/laravel-schema-contract/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/simba-jirira-source/laravel-schema-contract"><img src="https://img.shields.io/packagist/dt/simba-jirira-source/laravel-schema-contract.svg?style=flat-square" alt="Total Downloads"></a>
</p>

## Purpose

Laravel Schema Contract is developer tooling for Laravel applications. It answers a focused question for **v0.1.0**:

> Do my database columns and Eloquent model casts describe compatible data types?

The package discovers Eloquent models, reads live schema metadata from each model's effective connection and table, normalizes database and cast types, and reports contract violations with CI-friendly exit codes.

**v0.1.0 scope:** Database Schema ↔ Eloquent Model only.

The analyzer is read-only. It never mutates schema, models, or application data.

## Requirements

- PHP 8.3+
- Laravel 13+
- Composer 2+
- A reachable database connection for the models being analyzed

## Installation

Install the package as a development dependency:

```bash
composer require simba-jirira-source/laravel-schema-contract --dev
```

Laravel auto-discovery registers the service provider and Artisan command.

Publish the configuration file when you need custom discovery or ignore rules:

```bash
php artisan vendor:publish --tag=schema-contract-config
```

## Quick start

1. Ensure your application database schema is migrated.
2. Run the check against all discovered models:

```bash
php artisan schema-contract:check
```

3. Fix reported `ERROR` rows before merging. Treat `WARNING` rows as recommended improvements; they do not fail CI by default.

Analyze one model:

```bash
php artisan schema-contract:check User
php artisan schema-contract:check "App\\Models\\User"
```

Short class names work when they resolve to exactly one discovered model.

## Example output

Given a `users` table with a boolean `active` column, a decimal `credit_limit`, and a JSON `preferences` column, and a model that casts `credit_limit` to `integer` while leaving `preferences` uncased:

```text
App\Models\User
Table: users

PASS    active
        database: boolean
        cast: boolean

ERROR   credit_limit
        database: decimal(10,2)
        cast: integer
        suggested: decimal:2

WARNING preferences
        database: json
        cast: none
        suggested: array

Models inspected: 1
Columns inspected: 5
Errors: 1
Warnings: 1
Passed: 3
```

Severity meanings:

| Severity | Meaning | Fails CI by default |
|---|---|---|
| `ERROR` | High-confidence incompatible contract | Yes (exit code `1`) |
| `WARNING` | Suspicious or recommended improvement | No |
| `INFO` | Non-blocking information | No |

## Commands

| Command | Description |
|---|---|
| `php artisan schema-contract:check` | Analyze all discovered Eloquent models |
| `php artisan schema-contract:check {model}` | Analyze one model by FQCN or short name |

### Exit codes

| Code | Meaning |
|---|---|
| `0` | No blocking contract errors (warnings allowed) |
| `1` | One or more contract errors detected |
| `2` | Configuration, resolution, or runtime failure |

Examples of exit code `2`: unresolvable model argument, ambiguous short class name, missing database table for a targeted model.

When no models are discovered, the command exits `0` and prints a warning.

## Configuration

Configuration lives in `config/schema-contract.php` after publishing.

```php
return [
    'model_paths' => [
        app_path('Models'),
    ],

    'ignore_models' => [
        // App\Models\LegacyRecord::class,
    ],

    'ignore_columns' => [
        // 'users' => ['password', 'remember_token'],
    ],
];
```

| Option | Purpose |
|---|---|
| `model_paths` | Directories searched recursively for concrete Eloquent models. Falls back to `app_path('Models')` when empty. |
| `ignore_models` | Fully-qualified model classes excluded from bulk discovery and default checks. Targeted analysis by FQCN still works. |
| `ignore_columns` | Table-specific columns skipped during rule checks. Keys are database table names. |

## Supported databases and types

### Database drivers

| Driver | Support in v0.1.0 | CI verification |
|---|---|---|
| SQLite | First-class | Default test suite |
| MySQL / MariaDB | First-class | `database-compatibility` workflow |
| PostgreSQL | First-class | `database-compatibility` workflow |

See [docs/DATABASE_SUPPORT.md](docs/DATABASE_SUPPORT.md) for driver-specific metadata behavior, verified type mappings, and known limitations.

### Normalized database types

The package maps raw driver metadata to internal database types:

`boolean`, `integer`, `big_integer`, `small_integer`, `decimal`, `float`, `double`, `string`, `text`, `date`, `datetime`, `timestamp`, `json`, `uuid`, `enum`, `binary`, `unknown`

Unknown or incomplete metadata maps to `unknown` and produces conservative warnings rather than crashing analysis.

### Normalized cast types

Built-in and class-based casts normalize to:

`boolean`, `integer`, `float`, `double`, `decimal`, `string`, `array`, `object`, `collection`, `date`, `datetime`, `timestamp`, `enum`, `custom`, `unknown`

Custom cast classes are recognized without being misclassified as enums. Expressions such as `decimal:2` retain scale metadata.

### Contract rules (v0.1.0)

- Cast matches column type
- Decimal scale matches (when precision/scale metadata is available)
- JSON columns require a compatible cast (missing cast is a warning)
- Date/time columns require compatible casts (standard Laravel timestamp columns are excluded from noisy warnings)

## CI usage

Add the check to your pipeline after migrations are available (or against a migrated test database):

```yaml
- name: Install dependencies
  run: composer install --no-interaction --prefer-dist

- name: Run schema contract check
  run: php artisan schema-contract:check
```

The command exits `1` when contract errors exist, so a failing step blocks the pipeline without extra flags.

Warnings alone do not fail the command.

This repository validates the package itself with GitHub Actions:

| Workflow | Purpose |
|---|---|
| `tests.yml` | PHP 8.3/8.4/8.5 matrix, Composer validate, PHPStan, Pint, type coverage, Pest |
| `database-compatibility.yml` | MySQL and PostgreSQL grouped integration tests |

Forks and consuming applications can mirror the same quality commands during development:

```bash
composer check:composer
composer analyse
composer lint:check
composer test:types
vendor/bin/pest
```

## Limitations

**Not implemented in v0.1.0** (planned for later releases):

- FormRequest validation analysis
- API Resource analysis
- Livewire analysis
- Baseline generation and suppressions
- JSON or GitHub annotation output formats
- Automatic fixes

**Current analysis limits:**

- Driver metadata varies; missing precision/scale never produces false-positive decimal errors
- PostgreSQL native enums and extension types may map to `unknown`
- SQLite does not distinguish integer column sizes in schema metadata
- The package reports structural contract metadata only; it does not dump row data
- Ignored models are skipped during bulk checks but can still be analyzed when targeted explicitly

## Roadmap

| Version | Focus |
|---|---|
| v0.1 | Database ↔ Eloquent |
| v0.2 | Database ↔ Validation |
| v0.3 | Enums, defaults, nullability |
| v0.4 | Model ↔ FormRequest |
| v0.5 | API Resource contracts |
| v0.6 | Livewire contracts |
| v0.7 | CI / JSON / GitHub reporting |
| v0.8 | Baselines / suppression |
| v0.9 | Public extension API |
| v1.0 | Stable public API |

Each release is intended to be independently useful. See [docs/MASTER_SPEC.md](docs/MASTER_SPEC.md) for the full product specification.

## Testing

### Package development

Clone the repository and install dependencies:

```bash
composer install
```

Run the full quality suite:

```bash
composer test
```

Individual commands:

```bash
composer check:composer   # composer validate --strict
composer analyse          # PHPStan level 8 + Larastan
composer lint:check       # Laravel Pint
composer test:types       # Pest type coverage (100% minimum)
composer test:unit        # Pest parallel (optional smoke)
vendor/bin/pest           # Pest serial
```

`composer test` runs the release gate with serial Pest for reliable Testbench bootstrapping.

### Driver integration tests

SQLite tests run in the default suite. MySQL and PostgreSQL grouped tests require environment configuration. See [docs/DATABASE_SUPPORT.md](docs/DATABASE_SUPPORT.md#running-driver-integration-tests-locally).

## Contributing

Please see [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md). For significant changes, open an issue first.

## Security

The analyzer may include model names, table names, column names, and type metadata in output. It does not dump application records.

Please review [.github/SECURITY.md](.github/SECURITY.md) for vulnerability reporting.

## License

Laravel Schema Contract is open-source software licensed under the [MIT license](LICENSE.md).

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for release notes.
