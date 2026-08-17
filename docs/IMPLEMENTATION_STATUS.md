# Laravel Schema Contract — Implementation Status

> Updated by Phase 5 — Model Discovery (2026-08-17).

## Current Phase

**Phase 5 — Complete**

Next recommended phase: **Phase 6 — Schema Inspector** (await explicit maintainer instruction).

## Current State

The package can **discover concrete Eloquent model classes** from configured paths and **inspect/normalize** them separately from analysis orchestration. Schema inspection, compatibility rules, analyzer, and commands are not implemented yet.

### Phase 5 deliverables

- `ModelDiscoverer` contract and `EloquentModelDiscoverer` implementation
- Config-driven discovery from `schema-contract.model_paths` with `app_path('Models')` fallback
- Support for `schema-contract.ignore_models`
- Skips abstract classes, interfaces, traits, enums, and non-model classes
- Duplicate prevention by class name; sorted deterministic output
- Discovery fixtures and feature tests

### Quality command results (Phase 5, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 17 files; `--memory-limit=512M` in script |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 133 tests, 244 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── SchemaContractServiceProvider.php
├── Contracts/
│   ├── ModelDiscoverer.php
│   └── ModelInspector.php
├── Discovery/
│   └── EloquentModelDiscoverer.php
├── Inspectors/
│   └── EloquentModelInspector.php
├── DTO/
├── Enums/
└── Support/
```

### Discovery flow

```text
config model_paths (+ ignore_models)
        ↓
EloquentModelDiscoverer::discover()
        ↓
list<string> concrete model class names
```

Discovery returns **class names only** — no schema inspection, cast normalization, or rule execution. Analysis phases compose discovery + inspection later.

### Discovery behavior

| Behavior | Implementation |
|---|---|
| Default path | `app_path('Models')` when `model_paths` is empty |
| Nested namespaces | Recursive directory scan |
| Abstract / interface / trait / enum | Skipped via reflection |
| Non-Eloquent classes | Skipped via `is_subclass_of(Model::class)` |
| Duplicates | Deduplicated by FQCN across overlapping paths |
| Ignored models | Excluded via `ignore_models` config |
| Missing paths | Skipped silently |

## Dependencies

Unchanged from Phase 4 (`illuminate/database`, `illuminate/support`).

## Testing State

| Layer | Status |
|---|---|
| Feature — discovery | `tests/Feature/Discovery/EloquentModelDiscovererTest.php` |
| Feature — inspector | Phase 4 |
| Fixtures — discovery | `tests/Fixtures/Discovery/` |
| Unit — normalizers | Phases 3–4 |

## CI State

Unchanged from prior phases. PHPStan script now includes memory limit flag.

## Risks

1. **File parsing vs autoload** — FQCN extracted from file contents; requires `require_once` fallback for non-autoloaded paths.
2. **Multiple classes per file** — supported but uncommon; exotic layouts may need hardening.
3. **Discovery not wired to analyzer yet** — Phase 9 will orchestrate discovery + inspection.

## Conflicts With Master Specification

| Area | Status after Phase 5 |
|---|---|
| Model discovery | Resolved |
| Ignored models | Resolved (config-driven) |
| Discovery separate from analysis | Resolved |
| Schema inspector | Not started — Phase 6 |
| Primary command | Deferred — Phase 10 |

## Recommended Changes

### Phase 6 (when requested)

Schema inspector returning `TableDefinition` per model connection/table.

### Later phases

Phases 7–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–4 | Complete |
| 5 — Model discovery | **Complete** |
| 6 — Schema inspector | **Ready to begin** |
| 7–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 6)

Model discovery is implemented, tested, and kept separate from analysis. Schema inspection should not begin until Phase 6 is explicitly requested.
