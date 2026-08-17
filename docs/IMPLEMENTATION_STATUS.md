# Laravel Schema Contract — Implementation Status

> Updated by Phase 2 — Core Domain Types and DTOs (2026-08-17).

## Current Phase

**Phase 2 — Complete**

Next recommended phase: **Phase 3 — Database Type Normalization** (await explicit maintainer instruction).

## Current State

The package has a **typed v0.1 domain model** for schema/model contract analysis. No normalization, inspection, rules, analyzer, or command logic exists yet.

### Phase 2 deliverables

- `DatabaseType`, `CastType`, and `Severity` backed enums
- Readonly DTOs: `ColumnDefinition`, `CastDefinition`, `TableDefinition`, `ModelDefinition`, `ContractViolation`
- Unit tests covering enum cases, DTO construction, metadata preservation, nullable fields, precision/scale, and violation severity

### Quality command results (Phase 2, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 10 files |
| `composer test:types` | Pass | 100% type coverage on package source |
| `composer test:unit` | Pass | 56 tests, 103 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── SchemaContractServiceProvider.php
├── DTO/
│   ├── CastDefinition.php
│   ├── ColumnDefinition.php
│   ├── ContractViolation.php
│   ├── ModelDefinition.php
│   └── TableDefinition.php
└── Enums/
    ├── CastType.php
    ├── DatabaseType.php
    └── Severity.php
```

### Domain model summary

| Type | Role |
|---|---|
| `DatabaseType` | Normalized database column types (incl. `Unknown`) |
| `CastType` | Normalized Eloquent cast types (incl. `Custom`, `Unknown`) |
| `Severity` | `Error`, `Warning`, `Info` for contract violations |
| `ColumnDefinition` | Column name, type, nullable, default, length, precision, scale, original driver type |
| `CastDefinition` | Column, cast type, original expression, decimal scale, custom class |
| `TableDefinition` | Table name, connection, list of `ColumnDefinition` |
| `ModelDefinition` | Model class, connection, table, primary key, keyed `CastDefinition` map |
| `ContractViolation` | Severity, model/column context, message, suggested cast, type/scale metadata |

`ContractResult` and `AnalysisSummary` from the master spec are deferred to later analyzer phases.

### Service provider

Unchanged from Phase 1 — config merge/publish only.

## Dependencies

Unchanged from Phase 1. No new production dependencies added.

## Testing State

| Layer | Status |
|---|---|
| Feature | `tests/Feature/PackageFoundationTest.php` — Phase 1 foundation (5 tests) |
| Unit — Enums | `tests/Unit/Enums/` — all enum cases and backed-value restoration |
| Unit — DTOs | `tests/Unit/DTO/DomainDtoTest.php` — construction, metadata, readonly behavior |
| Architecture | `tests/ArchTest.php` — strict types, security/php presets |
| Normalization / rules / command | None (Phase 3+) |

## CI State

Unchanged from Phase 1. CI matrix / Laravel 13 alignment still deferred to Phase 14.

## Risks

1. **CI / composer constraint mismatch** — unchanged from Phase 1.
2. **`TableDefinition` / `ModelDefinition` collections** — use typed `list<ColumnDefinition>` and `array<string, CastDefinition>`; future phases must not degrade these to unstructured mixed arrays.
3. **No normalization yet** — enums/DTOs exist but raw driver/cast strings are not yet mapped (Phase 3–4).

## Conflicts With Master Specification

| Area | Status after Phase 2 |
|---|---|
| Core enums and v0.1 DTOs | Resolved |
| `ContractResult` / `AnalysisSummary` | Deferred — analyzer phase |
| Normalization, inspection, rules | Not started — Phase 3+ |
| Primary command | Deferred — Phase 10 |
| README / CHANGELOG | Open — Phase 15/16 |
| CI alignment | Open — Phase 14 |

## Recommended Changes

### Phase 3 (when requested)

Centralized database type normalization from raw driver strings into `DatabaseType` with metadata preservation.

### Later phases

Phases 4–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0 — Discovery audit | Complete |
| 1 — Package foundation | Complete |
| 2 — Core domain types and DTOs | **Complete** |
| 3 — Database type normalization | **Ready to begin** |
| 4–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 3)

Phase 2 domain types are in place with full unit coverage and passing quality suite. Normalization logic should not begin until Phase 3 is explicitly requested.
