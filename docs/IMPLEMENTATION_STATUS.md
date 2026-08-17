# Laravel Schema Contract — Implementation Status

> Updated by Phase 3 — Database Type Normalization (2026-08-17).

## Current Phase

**Phase 3 — Complete**

Next recommended phase: **Phase 4 — Eloquent Cast Inspection and Normalization** (await explicit maintainer instruction).

## Current State

The package can **normalize raw database column metadata** from representative SQLite, MySQL/MariaDB, and PostgreSQL driver type strings into typed `ColumnDefinition` values. Cast normalization, model discovery, schema inspection, rules, and commands are not implemented yet.

### Phase 3 deliverables

- `RawColumnMetadata` input DTO at the driver-metadata boundary
- `DatabaseColumnNormalizer` — centralized raw driver type → `ColumnDefinition` mapping
- Parsing for length, precision, and scale from driver type strings
- Safe degradation of unknown/custom types to `DatabaseType::Unknown`
- Unit tests for common types, cross-driver aliases, decimal metadata, nullable/default preservation, and unknown types

### Quality command results (Phase 3, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 12 files |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 96 tests, 169 assertions |

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
├── Enums/
│   ├── CastType.php
│   ├── DatabaseType.php
│   └── Severity.php
└── Support/
    ├── DatabaseColumnNormalizer.php
    └── RawColumnMetadata.php
```

### Normalization flow

```text
RawColumnMetadata (driver string + optional schema API metadata)
        ↓
DatabaseColumnNormalizer::normalize()
        ↓
ColumnDefinition (DatabaseType + preserved metadata)
```

Raw driver-string parsing and alias mapping are encapsulated in `DatabaseColumnNormalizer`. Callers pass structured metadata; no driver strings leak beyond `Support`.

### Supported normalization highlights

| Category | Examples |
|---|---|
| Integers | `int`, `integer`, `bigint`, `smallint`, `mediumint`, `serial`, `bigserial` |
| Boolean | `bool`, `boolean`, `tinyint(1)`, `bit(1)` |
| Decimals | `decimal`, `numeric`, `number` with `(precision, scale)` |
| Floats | `float`, `real`, `double`, `double precision` |
| Strings/text | `varchar`, `char`, `character varying`, `text`, `longtext` |
| Date/time | `date`, `datetime`, `timestamp`, `timestamptz` |
| Other | `json`, `jsonb`, `uuid`, `enum`, `binary`, `bytea`, `varbinary` |
| Unknown | `geography`, `geometry`, `set`, empty/unrecognized types |

Explicit schema API values for nullable, default, length, precision, and scale override parsed driver-string metadata when provided.

## Dependencies

Unchanged from Phase 1. No new production dependencies.

## Testing State

| Layer | Status |
|---|---|
| Feature | `tests/Feature/PackageFoundationTest.php` — Phase 1 foundation |
| Unit — Enums/DTOs | Phase 2 coverage |
| Unit — Normalization | `tests/Unit/Support/DatabaseColumnNormalizerTest.php` |
| Architecture | `tests/ArchTest.php` |
| Cast normalization / inspection | None (Phase 4+) |

## CI State

Unchanged from Phase 1. CI matrix alignment deferred to Phase 14.

## Risks

1. **Driver metadata variance** — real schema inspectors (Phase 6) may expose types in forms not yet covered; extend the normalizer incrementally with tests per driver.
2. **MySQL `SET` columns** — mapped to `Unknown`; may need explicit handling later.
3. **CI / Laravel 12 matrix** — unchanged from Phase 1.

## Conflicts With Master Specification

| Area | Status after Phase 3 |
|---|---|
| Database type normalization | Resolved |
| Unknown type degradation | Resolved |
| Metadata preservation | Resolved |
| Cast normalization | Not started — Phase 4 |
| Schema inspector integration | Not started — Phase 6 |
| Primary command | Deferred — Phase 10 |

## Recommended Changes

### Phase 4 (when requested)

Eloquent cast inspection and normalization into `CastDefinition` / `CastType`.

### Later phases

Phases 5–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0 — Discovery audit | Complete |
| 1 — Package foundation | Complete |
| 2 — Core domain types and DTOs | Complete |
| 3 — Database type normalization | **Complete** |
| 4 — Eloquent cast normalization | **Ready to begin** |
| 5–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 4)

Database column normalization is centralized, tested, and safe for unknown types. Cast inspection/normalization should not begin until Phase 4 is explicitly requested.
