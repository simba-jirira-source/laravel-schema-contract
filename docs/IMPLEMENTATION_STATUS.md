# Laravel Schema Contract — Implementation Status

> Updated by Phase 12 — Database Compatibility Hardening (2026-08-17).

## Current Phase

**Phase 12 — Complete**

Next recommended phase: **Phase 13 — Static Analysis and Architecture Quality** (await explicit maintainer instruction).

## Current State

Database driver metadata is hardened for SQLite, MySQL/MariaDB, and PostgreSQL with isolated driver enrichment, expanded integration coverage, and explicit documentation of verified support and limitations.

### Phase 12 deliverables

- `ColumnTypeParser` — shared precision/scale/length parsing from driver type strings
- `DatabaseDriver` enum and `DriverColumnMetadataEnricher` — isolated SQLite/MySQL/PostgreSQL metadata enrichment
- `SchemaColumnMetadataFactory` and `EloquentSchemaInspector` pass connection driver into enrichment
- `DatabaseColumnNormalizer` extended for PostgreSQL `citext` and MySQL `year`
- SQLite integration coverage for booleans, integers, decimal, JSON, UUID-as-string, datetime, and unknown types
- MySQL/PostgreSQL grouped integration tests (skip locally; run in `database-compatibility` CI workflow)
- Unit tests for driver metadata fixtures across all three drivers
- `docs/DATABASE_SUPPORT.md` documenting verified behavior and genuine limitations
- `.github/workflows/database-compatibility.yml` for MySQL and PostgreSQL service verification

### Quality command results (Phase 12, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 44 files; `--memory-limit=512M` |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 272 passed, 6 skipped (mysql/pgsql groups), 716 assertions |

## Existing Architecture

### Database normalization flow

```text
Schema::getColumns()
        ↓
SchemaColumnMetadataFactory (driver-aware)
        ↓
DriverColumnMetadataEnricher (sqlite / mysql / pgsql)
        ↓
DatabaseColumnNormalizer + ColumnTypeParser
        ↓
ColumnDefinition → ContractAnalyzer / rules
```

### Driver verification matrix

| Driver | Default CI | Dedicated CI workflow | Integration tests |
|---|---|---|---|
| SQLite | Yes | n/a | Always run |
| MySQL/MariaDB | Skipped locally | `database-compatibility.yml` | `--group=mysql` |
| PostgreSQL | Skipped locally | `database-compatibility.yml` | `--group=pgsql` |

## Dependencies

Unchanged from Phase 4.

## Testing State

| Layer | Status |
|---|---|
| Unit — column type parser / driver enricher | `tests/Unit/Support/` |
| Integration — SQLite driver | `tests/Integration/Database/SqliteDriverCompatibilityTest.php` |
| Integration — MySQL driver | `tests/Integration/Database/MySqlDriverCompatibilityTest.php` |
| Integration — PostgreSQL driver | `tests/Integration/Database/PostgresDriverCompatibilityTest.php` |
| CI — database compatibility | `.github/workflows/database-compatibility.yml` |

## CI State

- Existing `tests.yml` unchanged (SQLite-focused matrix)
- New `database-compatibility.yml` verifies MySQL and PostgreSQL integration groups on Ubuntu with service containers

## Risks

1. **SQLite integer aliasing** — all integer sizes report as `integer`; documented in `docs/DATABASE_SUPPORT.md`.
2. **PostgreSQL native enums** — not fully verified across all deployment styles in v0.1.0.
3. **Parallel Testbench flakiness** — occasional config bootstrap race on Windows under `--parallel`.

## Conflicts With Master Specification

| Area | Status after Phase 12 |
|---|---|
| First-class SQLite/MySQL/PostgreSQL support | Resolved for v0.1 metadata normalization scope |
| Graceful unknown metadata degradation | Resolved |
| README driver documentation | Deferred — Phase 15 |

## Recommended Changes

### Phase 13 (when requested)

Add/refine PHPStan/Larastan architecture checks and review public API quality.

### Later phases

Phases 14–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–11 | Complete |
| 12 — DB hardening | **Complete** |
| 13 — Static analysis / architecture | **Ready to begin** |
| 14–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 13)

Driver-specific schema normalization is hardened, tested, and documented. Static analysis and architecture quality work should not begin until Phase 13 is explicitly requested.
