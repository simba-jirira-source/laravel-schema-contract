# Laravel Schema Contract — Phased Implementation Plan

## Operating Rule

Read `docs/MASTER_SPEC.md` before every phase.

Implement exactly one phase at a time.

Do not begin the next phase until the maintainer explicitly asks.

The initial release target is:

```text
v0.1.0 — Database Schema ↔ Eloquent Model Contract Analysis
```

## Phase Index

| Phase | Purpose |
|---|---|
| 0 | Repository discovery and architecture audit |
| 1 | Package foundation |
| 2 | Core domain types and DTOs |
| 3 | Database type normalization |
| 4 | Eloquent cast inspection and normalization |
| 5 | Model discovery |
| 6 | Schema inspector |
| 7 | Compatibility engine |
| 8 | Contract rules |
| 9 | Contract analyzer |
| 10 | Artisan command |
| 11 | Configuration and ignore controls |
| 12 | Database compatibility hardening |
| 13 | Static analysis and architecture quality |
| 14 | CI |
| 15 | README and documentation |
| 16 | v0.1.0 release-readiness audit |
| 17 | Post-v0.1 architecture review |

## Phase 0 — Repository Discovery and Architecture Audit

Do not implement functionality.

Inspect the complete repository and record:

- PHP/Laravel requirements
- namespace/autoloading
- package auto-discovery
- tests/Testbench
- Pint
- PHPStan/Larastan
- service provider
- commands
- CI
- docs
- README
- CHANGELOG
- LICENSE
- package conflicts with the master spec

Create/update `docs/IMPLEMENTATION_STATUS.md`.

End with `READY` or `BLOCKED`.

## Phase 1 — Package Foundation

Ensure a clean Laravel 13+/PHP 8.3+ package foundation:

- Composer metadata
- namespace
- service provider
- auto-discovery
- config registration/publishing
- Pest
- package Testbench
- Pint

Do not implement analyzer logic.

## Phase 2 — Core Domain Types and DTOs

Add only the typed domain model required for v0.1:

- DatabaseType
- CastType
- Severity
- ModelDefinition
- TableDefinition
- ColumnDefinition
- CastDefinition
- ContractViolation

Prefer immutable DTOs and typed properties.

## Phase 3 — Database Type Normalization

Create centralized raw-schema normalization.

Support representative SQLite/MySQL/MariaDB/PostgreSQL types.

Preserve nullable/default/length/precision/scale where available.

Unknown types map safely to `UNKNOWN`.

## Phase 4 — Eloquent Cast Inspection and Normalization

Inspect model metadata and normalize built-in Laravel casts.

Support boolean/integer/float/double/decimal/string/array/json/object/collection/date/datetime/immutable date-time/timestamp.

Recognize enum and custom casts safely.

## Phase 5 — Model Discovery

Discover concrete Eloquent models from configurable paths.

Support nested namespaces, ignore abstract/non-model classes, prevent duplicates, and support ignored models.

## Phase 6 — Schema Inspector

Inspect each model's effective connection/table and return typed `TableDefinition` metadata.

Handle custom table/connection, missing table, unknown metadata, and unsupported types gracefully.

## Phase 7 — Compatibility Engine

Create a centralized compatibility matrix/service.

Return meaningful compatibility information rather than scattered booleans.

Handle no-cast cases conservatively.

## Phase 8 — Contract Rules

Create the rule contract/registry and initial rules:

- cast matches column type
- decimal scale matches
- JSON column has compatible cast
- date/time compatibility

Rules return structured violations and contain no CLI rendering.

## Phase 9 — Contract Analyzer

Build presentation-independent orchestration:

- inspect model
- inspect schema
- execute rules
- collect violations
- calculate summaries
- expose programmatic results

## Phase 10 — Artisan Command

Implement:

```bash
php artisan schema-contract:check
```

and targeted model analysis.

Render concise output and implement exit codes:

- 0 clean/no blocking errors
- 1 contract errors
- 2 runtime/config failure

Warnings do not fail by default.

## Phase 11 — Configuration and Ignore Controls

Finalize minimal config:

- model paths
- ignored models
- ignored columns

Do not add baseline support yet.

## Phase 12 — Database Compatibility Hardening

Harden SQLite/MySQL/MariaDB/PostgreSQL behavior.

Test driver differences for boolean, integers, decimal, JSON/JSONB, UUID, timestamps, enum, and text types.

Document any unverified support honestly.

## Phase 13 — Static Analysis and Architecture Quality

Add/refine PHPStan/Larastan and architecture checks.

Review mixed usage, arrays, public APIs, DTOs, reflection, exceptions, dependencies, and package exports.

## Phase 14 — CI

GitHub Actions should validate pull requests, `development`, and `main`.

Run:

- composer validate
- Pest
- Pint
- static analysis
- supported PHP/Laravel matrix as practical
- DB matrix where practical

Do not publish releases.

## Phase 15 — README and Documentation

Document only existing v0.1 functionality:

- purpose
- install
- quick start
- examples
- commands
- configuration
- supported DBs/types
- CI use
- limitations
- roadmap
- contributing/security/license

## Phase 16 — v0.1.0 Release-Readiness Audit

Audit the entire repo against the master spec.

Run the full quality suite.

Update CHANGELOG using Keep a Changelog with an empty `[Unreleased]` above `[0.1.0]`.

Do not tag, release, push tags, or publish.

## Phase 17 — Post-v0.1 Architecture Review

Do not implement v0.2.

Evaluate:

- rule API
- DTO stability
- extension points
- false positives
- DB support
- performance
- DX
- configuration
- command output

Record findings and recommended refactoring before Database ↔ Validation work begins.

## Global Acceptance Rules

For every phase:

1. Inspect before modifying.
2. Implement only the requested phase.
3. Preserve completed behavior.
4. Add tests with implementation.
5. Do not hide test/static-analysis failures.
6. Avoid speculative abstractions.
7. Keep analyzer logic independent from CLI presentation.
8. Prefer conservative severity when uncertain.
9. Never create tags/releases/Packagist publication without explicit maintainer instruction.
10. End with a phase report and stop.
