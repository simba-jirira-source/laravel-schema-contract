# Laravel Schema Contract — Implementation Status

> Updated by Phase 4 — Eloquent Cast Inspection and Normalization (2026-08-17).

## Current Phase

**Phase 4 — Complete**

Next recommended phase: **Phase 5 — Model Discovery** (await explicit maintainer instruction).

## Current State

The package can **inspect Eloquent models** and **normalize cast metadata** into typed `ModelDefinition` / `CastDefinition` values, alongside Phase 3 database column normalization. Model discovery, schema inspection, compatibility rules, analyzer, and commands are not implemented yet.

### Phase 4 deliverables

- `ModelInspector` contract and `EloquentModelInspector` implementation
- `CastNormalizer` — centralized cast expression → `CastDefinition` mapping
- Production dependency on `illuminate/database` for Eloquent model inspection
- Test fixtures: models, enum, and custom cast class
- Unit and feature tests for built-in casts, decimal scale, JSON/date casts, enum/custom casts, custom table/connection

### Quality command results (Phase 4, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 15 files |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 124 tests, 232 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── SchemaContractServiceProvider.php
├── Contracts/
│   └── ModelInspector.php
├── Inspectors/
│   └── EloquentModelInspector.php
├── DTO/
│   └── …
├── Enums/
│   └── …
└── Support/
    ├── CastNormalizer.php
    ├── DatabaseColumnNormalizer.php
    └── RawColumnMetadata.php
```

### Inspection flow

```text
model class string
        ↓
EloquentModelInspector::inspect()
        ↓
ModelDefinition (connection, table, primary key, CastDefinition map)
        ↑
CastNormalizer per cast entry from Model::getCasts()
```

Reflection and cast string parsing are localized to `EloquentModelInspector` (instantiation) and `CastNormalizer` (expression parsing).

### Cast normalization highlights

| Input | Normalized type |
|---|---|
| `bool`, `boolean` | `CastType::Boolean` |
| `int`, `integer` | `CastType::Integer` |
| `float`, `real` | `CastType::Float` |
| `double` | `CastType::Double` |
| `decimal`, `decimal:n` | `CastType::Decimal` (+ scale) |
| `string`, `array`, `json`, `object`, `collection` | Matching cast types (`json` → `Array`) |
| `date`, `datetime`, `immutable_*`, `timestamp` | Date/time cast types |
| PHP enum class | `CastType::Enum` |
| `CastsAttributes` / `Castable` classes | `CastType::Custom` |
| Unrecognized (e.g. `hashed`) | `CastType::Unknown` |

## Dependencies

### Production

| Package | Constraint |
|---|---|
| `php` | `^8.3` |
| `illuminate/database` | `^13.0` |
| `illuminate/support` | `^13.0` |

## Testing State

| Layer | Status |
|---|---|
| Feature — foundation | Phase 1 package boot/config tests |
| Feature — inspector | `tests/Feature/Inspectors/EloquentModelInspectorTest.php` |
| Unit — cast normalizer | `tests/Unit/Support/CastNormalizerTest.php` |
| Unit — column normalizer | Phase 3 |
| Fixtures | `tests/Fixtures/Models`, `Enums`, `Casts` |

## CI State

Unchanged from Phase 1/3. CI matrix alignment deferred to Phase 14.

## Risks

1. **Laravel built-in cast evolution** — casts like `hashed` map to `Unknown` until explicitly supported; extend incrementally.
2. **Array cast syntax** — only class-first array definitions normalized; complex Laravel 11+ cast arrays may need Phase 12 hardening.
3. **Model boot side effects** — inspector instantiates models without DB writes; exotic model constructors may need future guards.

## Conflicts With Master Specification

| Area | Status after Phase 4 |
|---|---|
| Model inspection | Resolved |
| Cast normalization | Resolved |
| Model discovery (paths, ignores) | Not started — Phase 5 |
| Schema inspector | Not started — Phase 6 |
| Primary command | Deferred — Phase 10 |

## Recommended Changes

### Phase 5 (when requested)

Configurable Eloquent model discovery from `model_paths` with ignore support.

### Later phases

Phases 6–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–3 | Complete |
| 4 — Eloquent cast inspection | **Complete** |
| 5 — Model discovery | **Ready to begin** |
| 6–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 5)

Cast inspection and normalization are implemented and tested. Model discovery should not begin until Phase 5 is explicitly requested.
