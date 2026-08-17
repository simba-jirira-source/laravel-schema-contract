# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/simba-jirira-source/laravel-schema-contract/compare/v0.1.0...HEAD)

## [0.1.0] - 2026-08-17

Initial release. Database schema ↔ Eloquent model contract analysis for Laravel 13+.

### Added

- `schema-contract:check` Artisan command with bulk and targeted model analysis
- Eloquent model discovery from configurable `model_paths`
- Schema inspection through Laravel's `Schema::getColumns()` API with driver-aware metadata enrichment
- Eloquent cast inspection and normalization, including enum and custom cast classes
- Centralized database and cast type normalization (`DatabaseType`, `CastType`)
- Compatibility matrix and contract rules:
  - cast matches column type
  - decimal scale matches (when metadata is available)
  - JSON column compatible cast checks
  - date/time compatible cast checks
- `ContractAnalyzer` for presentation-independent programmatic analysis
- Structured results with `ERROR`, `WARNING`, and `INFO` severities
- Human-readable console output with per-column pass/error/warning lines and analysis summary
- CI-friendly exit codes: `0` clean, `1` contract errors, `2` runtime/configuration failure
- Publishable configuration (`schema-contract-config` tag) with `model_paths`, `ignore_models`, and `ignore_columns`
- SQLite, MySQL/MariaDB, and PostgreSQL driver support with graceful degradation for unknown metadata
- Documentation: README, `docs/DATABASE_SUPPORT.md`, bundled Boost adoption skill
- GitHub Actions CI: PHP 8.3/8.4/8.5 compatibility matrix and MySQL/PostgreSQL integration workflows

### Developer tooling

- Pest test suite with unit, feature, integration, and architecture tests
- PHPStan level 8 with Larastan
- Laravel Pint formatting and 100% Pest type coverage on package source

[0.1.0]: https://github.com/simba-jirira-source/laravel-schema-contract/releases/tag/v0.1.0
