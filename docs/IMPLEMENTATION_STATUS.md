# Laravel Schema Contract — Implementation Status

> Updated by Phase 6 — Schema Inspector (2026-08-17).

## Current Phase

**Phase 6 — Complete**

Next recommended phase: **Phase 7 — Compatibility Engine** (await explicit maintainer instruction).

## Current State

The package can **discover concrete Eloquent models**, **inspect/normalize model casts**, and **inspect normalized database schema metadata** per model connection/table. Compatibility rules, analyzer orchestration, and commands are not implemented yet.

### Phase 6 deliverables

- `SchemaInspector` contract and `EloquentSchemaInspector` implementation
- `SchemaColumnMetadataFactory` to translate Laravel schema column arrays into `RawColumnMetadata` at the support boundary
- `MissingTableException` for absent model tables
- Uses each model's effective connection and table from `ModelDefinition`
- Returns typed `TableDefinition` / `ColumnDefinition` metadata (type, nullable, default, length, precision, scale when available)
- Unknown driver types map to `DatabaseType::Unknown` without crashing inspection
- Raw schema arrays stay inside normalization boundaries — analyzer consumers receive DTOs only
- Integration tests with temporary SQLite schemas (boolean, integer, decimal, string/text, JSON, date/datetime, nullability/defaults, custom tables/connections, missing tables, unsupported types)
- Unit tests for `SchemaColumnMetadataFactory`

### Quality command results (Phase 6, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 21 files; `--memory-limit=512M` |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 144 tests, 291 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── SchemaContractServiceProvider.php
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
├── Enums/
└── Support/
    ├── CastNormalizer.php
    ├── DatabaseColumnNormalizer.php
    ├── RawColumnMetadata.php
    └── SchemaColumnMetadataFactory.php
```

### Inspection flow

```text
ModelDefinition (connection, table)
        ↓
EloquentSchemaInspector::inspect()
        ↓
Schema::connection(...)->getColumns()
        ↓
SchemaColumnMetadataFactory → RawColumnMetadata
        ↓
DatabaseColumnNormalizer → ColumnDefinition[]
        ↓
TableDefinition
```

Schema inspection is separate from discovery, model inspection, and future analysis orchestration.

### Schema inspection behavior

| Behavior | Implementation |
|---|---|
| Effective connection/table | Taken from `ModelDefinition` |
| Missing table | `MissingTableException` |
| Custom connection | `Schema::connection($model->connection)` |
| Custom table name | Uses `$model->table` |
| Unknown driver types | `DatabaseType::Unknown` |
| Precision/scale | Parsed from driver type when present; null when driver omits them |
| Raw metadata containment | Laravel column arrays converted in `SchemaColumnMetadataFactory` only |

## Dependencies

Unchanged from Phase 4 (`illuminate/database`, `illuminate/support`).

## Testing State

| Layer | Status |
|---|---|
| Integration — schema inspector | `tests/Integration/Inspectors/EloquentSchemaInspectorTest.php` |
| Unit — schema metadata factory | `tests/Unit/Support/SchemaColumnMetadataFactoryTest.php` |
| Unit — column normalizer | Phase 3 |
| Feature — discovery / model inspector | Phases 4–5 |
| Fixtures — schema | `tests/Fixtures/Schema/` |

Testbench `TestCase` configures in-memory SQLite connections (`testing`, `analytics`) with `use_native_json` for realistic JSON column typing in integration tests.

## CI State

Unchanged from prior phases. PHPStan script includes memory limit flag.

## Risks

1. **SQLite driver fidelity** — Laravel SQLite grammars may omit decimal precision/scale and map JSON to `text` unless `use_native_json` is enabled; integration tests account for both declared and omitted metadata.
2. **Parallel Pest flakiness** — occasional Testbench skeleton config race on Windows under `--parallel`; re-run or `composer prepare` if config bootstrap fails.
3. **Schema inspector not wired to analyzer yet** — Phase 9 will orchestrate discovery + model + schema inspection.

## Conflicts With Master Specification

| Area | Status after Phase 6 |
|---|---|
| Schema inspector | Resolved |
| Typed table/column metadata | Resolved |
| Missing table handling | Resolved |
| Raw metadata boundaries | Resolved |
| Compatibility engine | Not started — Phase 7 |
| Primary command | Deferred — Phase 10 |

## Recommended Changes

### Phase 7 (when requested)

Compatibility engine returning structured compatibility information between normalized column types and casts.

### Later phases

Phases 8–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–5 | Complete |
| 6 — Schema inspector | **Complete** |
| 7 — Compatibility engine | **Ready to begin** |
| 8–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 7)

Schema inspection is implemented, tested, and kept separate from analysis. The compatibility engine should not begin until Phase 7 is explicitly requested.
