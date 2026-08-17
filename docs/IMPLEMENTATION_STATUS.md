# Laravel Schema Contract — Implementation Status

> Updated by Phase 7 — Compatibility Engine (2026-08-17).

## Current Phase

**Phase 7 — Complete**

Next recommended phase: **Phase 8 — Contract Rules** (await explicit maintainer instruction).

## Current State

The package can **discover models**, **inspect/normalize casts and schema metadata**, and **evaluate centralized type compatibility** between normalized column and cast types. Contract rules, analyzer orchestration, and commands are not implemented yet.

### Phase 7 deliverables

- `CompatibilityState` enum (`compatible`, `incompatible`, `uncertain`)
- `CompatibilityResult` DTO with state, reason, suggested severity, suggested cast, and optional scale metadata
- `TypeCompatibilityMatrix` centralized compatibility service in `src/Compatibility/`
- Master-spec matrix coverage for boolean, integer family, decimal, float/double, string/text, date/datetime/timestamp, JSON, UUID, and enum columns
- Conservative no-cast handling (JSON warns; string/text/uuid pass; binary uncertain)
- Custom-cast and unknown-type cases return `Uncertain` without crashing
- Decimal scale mismatch detection when both database and cast expose scale
- Suggested cast generation for incompatible pairings (e.g. `decimal:2`, `array`)
- Matrix-style Pest unit tests for valid, invalid, uncertain, no-cast, custom, and unknown combinations

### Quality command results (Phase 7, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 24 files; `--memory-limit=512M` |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 189 tests, 484 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── SchemaContractServiceProvider.php
├── Compatibility/
│   └── TypeCompatibilityMatrix.php
├── Contracts/
│   ├── ModelDiscoverer.php
│   ├── ModelInspector.php
│   └── SchemaInspector.php
├── Discovery/
│   └── EloquentModelDiscoverer.php
├── Exceptions/
│   └── MissingTableException.php
├── Inspectors/
│   ├── EloquentModelInspector.php
│   └── EloquentSchemaInspector.php
├── DTO/
│   ├── CompatibilityResult.php
│   └── ...
├── Enums/
│   ├── CompatibilityState.php
│   └── ...
└── Support/
```

### Compatibility flow

```text
ColumnDefinition + optional CastDefinition
        ↓
TypeCompatibilityMatrix::compare()
        ↓
CompatibilityResult (state, reason, severity, suggested cast)
```

The matrix is presentation-independent. Phase 8 rules will consume it to emit `ContractViolation` records.

### Compatibility behavior

| Scenario | State | Suggested severity |
|---|---|---|
| Spec-valid cast pairing | `Compatible` | — |
| Spec-invalid cast pairing | `Incompatible` | `Error` |
| Decimal scale mismatch (both known) | `Incompatible` | `Error` |
| JSON column, no cast | `Uncertain` | `Warning` |
| String/text/uuid, no cast | `Compatible` | — |
| Unknown database type | `Uncertain` | `Warning` |
| Custom cast | `Uncertain` | `Info` |
| Unrecognized cast expression | `Uncertain` | `Warning` |
| Binary column | `Uncertain` | `Info` |

## Dependencies

Unchanged from Phase 4 (`illuminate/database`, `illuminate/support`).

## Testing State

| Layer | Status |
|---|---|
| Unit — compatibility matrix | `tests/Unit/Compatibility/TypeCompatibilityMatrixTest.php` |
| Unit — compatibility enum | `tests/Unit/Enums/CompatibilityStateTest.php` |
| Unit — DTO | `CompatibilityResult` covered in `DomainDtoTest.php` |
| Integration — schema inspector | Phase 6 |
| Feature — discovery / model inspector | Phases 4–5 |

## CI State

Unchanged from prior phases.

## Risks

1. **Binary columns** — treated as uncertain because the master spec does not define a strict cast mapping.
2. **No-cast numeric/date columns** — intentionally pass to avoid false positives; JSON without cast warns per spec.
3. **Matrix not wired to rules yet** — Phase 8 will translate results into structured violations.

## Conflicts With Master Specification

| Area | Status after Phase 7 |
|---|---|
| Centralized compatibility | Resolved |
| Conservative false-positive policy | Resolved |
| Decimal scale detection | Resolved (when metadata available) |
| Contract rules | Not started — Phase 8 |
| Primary command | Deferred — Phase 10 |

## Recommended Changes

### Phase 8 (when requested)

Contract rule contract/registry consuming `TypeCompatibilityMatrix` for violations.

### Later phases

Phases 9–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–6 | Complete |
| 7 — Compatibility engine | **Complete** |
| 8 — Contract rules | **Ready to begin** |
| 9–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 8)

Type compatibility is centralized, tested, and kept separate from rule orchestration and CLI output. Contract rules should not begin until Phase 8 is explicitly requested.
