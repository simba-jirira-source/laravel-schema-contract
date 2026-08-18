# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/simba-jirira-source/laravel-schema-contract/compare/v0.1.2...HEAD)

## [0.1.2] - 2026-08-18

Maintenance release: release and documentation consistency after v0.1.1. No functional or API changes.

### Changed

- Reconciled `docs/IMPLEMENTATION_STATUS.md` with released **v0.1.1** state and v0.1.2 preparation status
- Updated README, `docs/DATABASE_SUPPORT.md`, and bundled Boost skill to use **v0.1.x** for maintenance-line behaviour statements
- Corrected internal Laravel compatibility wording to **Laravel 13.x** in `docs/MASTER_SPEC.md` and `docs/IMPLEMENTATION_PLAN.md`
- Improved Composer distribution export hygiene by excluding `/prompts` and internal v0.1.x stabilization docs without excluding consumer-facing `docs/DATABASE_SUPPORT.md`

## [0.1.1] - 2026-08-18

Maintenance release: documentation, distribution, security, and repository-quality fixes. No functional or API changes.

### Fixed

- Corrected Laravel compatibility wording to **Laravel 13.x** in consumer-facing documentation (matching Composer `^13.0` constraints)
- Fixed README terminology (`uncast` instead of `uncased`)

### Changed

- Corrected PHP namespace references in `docs/DATABASE_SUPPORT.md`
- Updated README links to repository-only documentation to absolute GitHub URLs
- Improved Composer distribution export hygiene by excluding `docs/ARCHITECTURE_REVIEW.md`
- Updated implementation status documentation after the v0.1.0 release
- Improved security documentation (`.github/SECURITY.md`) and bug-report issue template diagnostic fields
- Updated bundled Boost skill Laravel compatibility wording to Laravel 13.x

## [0.1.0] - 2026-08-17

Initial release. Database schema ↔ Eloquent model contract analysis for Laravel 13.x.

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

[0.1.2]: https://github.com/simba-jirira-source/laravel-schema-contract/releases/tag/v0.1.2
[0.1.1]: https://github.com/simba-jirira-source/laravel-schema-contract/releases/tag/v0.1.1
[0.1.0]: https://github.com/simba-jirira-source/laravel-schema-contract/releases/tag/v0.1.0
