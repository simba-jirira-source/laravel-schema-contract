# Laravel Schema Contract — Implementation Status

> Updated by Phase 8 — Contract Rules (2026-08-17).

## Current Phase

**Phase 8 — Complete**

Next recommended phase: **Phase 9 — Contract Analyzer** (await explicit maintainer instruction).

## Current State

The package can **discover models**, **inspect/normalize casts and schema**, **evaluate type compatibility**, and **execute contract rules** that return structured `ContractViolation` records. Analyzer orchestration and commands are not implemented yet.

### Phase 8 deliverables

- `ContractRule` contract with `identifier()` and `analyze(ModelDefinition, ColumnDefinition)`
- `RuleRegistry` with built-in defaults and optional rule registration
- `ViolationFactory` for consistent violation metadata from compatibility results
- Extended `ContractViolation` with `rule`, `table`, `connection`, and `modelCast` fields
- Initial rules:
  - `CastMatchesColumnTypeRule` — general type compatibility via `TypeCompatibilityMatrix`
  - `DecimalScaleMatchesRule` — decimal scale mismatch when both sides expose scale
  - `JsonColumnHasCompatibleCastRule` — JSON cast recommendations and incompatible cast errors
  - `DateColumnHasCompatibleCastRule` — date/datetime/timestamp compatibility; skips `created_at`/`updated_at`
- Conservative false-positive policy: `Info`-level uncertain cases (custom casts) emit no violation from general rules; JSON without cast warns; unknown database types warn
- Independent Pest tests per rule plus registry tests

### Quality command results (Phase 8, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 31 files; `--memory-limit=512M` |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 218 tests, 561 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── Compatibility/
│   └── TypeCompatibilityMatrix.php
├── Contracts/
│   ├── ContractRule.php
│   ├── ModelDiscoverer.php
│   ├── ModelInspector.php
│   └── SchemaInspector.php
├── Rules/
│   ├── CastMatchesColumnTypeRule.php
│   ├── DateColumnHasCompatibleCastRule.php
│   ├── DecimalScaleMatchesRule.php
│   ├── JsonColumnHasCompatibleCastRule.php
│   ├── RuleRegistry.php
│   └── Support/
│       └── ViolationFactory.php
├── DTO/
│   └── ContractViolation.php (extended)
└── ...
```

### Rule execution flow

```text
ModelDefinition + ColumnDefinition
        ↓
RuleRegistry::analyze() / ContractRule::analyze()
        ↓
TypeCompatibilityMatrix (where applicable)
        ↓
list<ContractViolation>
```

Rules return structured violations only — no CLI rendering.

### Rule responsibilities

| Rule | Scope | Typical severity |
|---|---|---|
| `cast_matches_column_type` | Non-JSON, non-date/time columns; defers decimal scale | `Error` / `Warning` |
| `decimal_scale_matches` | Decimal column + decimal cast with known scales | `Error` |
| `json_column_has_compatible_cast` | JSON columns only | `Warning` (no cast) / `Error` (wrong cast) |
| `date_column_has_compatible_cast` | Date/datetime/timestamp (except standard timestamps) | `Error` |

## Dependencies

Unchanged from Phase 4.

## Testing State

| Layer | Status |
|---|---|
| Unit — each contract rule | `tests/Unit/Rules/*RuleTest.php` |
| Unit — rule registry | `tests/Unit/Rules/RuleRegistryTest.php` |
| Unit — compatibility matrix | Phase 7 |
| Test fixtures | `tests/Support/RuleTestFixtures.php` |

## CI State

Unchanged from prior phases.

## Risks

1. **Rule overlap** — specialized rules own JSON/date/decimal-scale cases; general rule defers to avoid duplicate violations.
2. **Custom casts** — intentionally produce no blocking violations; compatibility cannot be verified automatically.
3. **Rules not wired to analyzer yet** — Phase 9 will orchestrate inspection + rule execution.

## Conflicts With Master Specification

| Area | Status after Phase 8 |
|---|---|
| Contract rules + registry | Resolved |
| Structured violations | Resolved |
| JSON / date / decimal rules | Resolved |
| Contract analyzer | Not started — Phase 9 |
| Primary command | Deferred — Phase 10 |

## Recommended Changes

### Phase 9 (when requested)

Presentation-independent analyzer orchestrating discovery, inspection, and rule execution.

### Later phases

Phases 10–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–7 | Complete |
| 8 — Contract rules | **Complete** |
| 9 — Contract analyzer | **Ready to begin** |
| 10–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 9)

Contract rules are implemented, tested independently, and kept separate from analyzer orchestration and CLI output. The contract analyzer should not begin until Phase 9 is explicitly requested.
