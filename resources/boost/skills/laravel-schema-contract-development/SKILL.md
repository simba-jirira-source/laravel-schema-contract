---
name: laravel-schema-contract-development
description: >
  Configure and run Laravel Schema Contract v0.1 checks for database schema ↔ Eloquent cast compatibility in Laravel 13.x applications.
license: MIT
metadata:
  author: simba-jirira-source
---

# Laravel Schema Contract

Use this skill when a Laravel application needs to verify that Eloquent model casts match live database column types.

## Primary Goal

Install and run `simba-jirira-source/laravel-schema-contract` with the smallest correct configuration, then interpret command output and CI exit codes.

## Workflow

### 1. Confirm prerequisites

- Laravel 13.x and PHP 8.3+
- Database migrated and reachable for models under analysis
- Models live under configurable discovery paths (default `app/Models`)

### 2. Install

```bash
composer require simba-jirira-source/laravel-schema-contract --dev
```

The service provider and `schema-contract:check` command are auto-discovered.

### 3. Publish config when needed

```bash
php artisan vendor:publish --tag=schema-contract-config
```

Configure:

- `model_paths` — directories to scan for concrete Eloquent models
- `ignore_models` — FQCNs excluded from bulk checks
- `ignore_columns` — table => column names skipped during analysis

### 4. Run analysis

```bash
php artisan schema-contract:check
php artisan schema-contract:check User
php artisan schema-contract:check "App\\Models\\User"
```

Exit codes:

- `0` — no blocking errors (warnings allowed)
- `1` — contract errors detected
- `2` — resolution or runtime failure

### 5. Wire into CI

Run after migrations against a test database:

```yaml
- run: php artisan schema-contract:check
```

Treat non-zero exit as a blocking failure.

## Rules, References, and Templates

- `README.md` — user-facing documentation
- `docs/DATABASE_SUPPORT.md` — driver-specific behavior and limitations
- `config/schema-contract.php` — publishable configuration

## Examples

### Ignore sensitive or legacy columns

```php
'ignore_columns' => [
    'users' => ['password', 'remember_token'],
],
```

### Analyze one model in CI while excluding others locally

Use `ignore_models` for bulk runs, or pass a model argument in CI for targeted checks.

## Anti-patterns

- Do not expect FormRequest, API Resource, Livewire, baseline, JSON, or GitHub annotation features in v0.1.0 — they are not implemented.
- Do not publish non-existent tags; only `schema-contract-config` exists.
- Do not treat warnings as CI failures unless your team policy requires it; only errors exit `1` by default.
- Do not assume all models use the default connection; the analyzer respects each model's effective connection and table.
