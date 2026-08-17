# Laravel Schema Contract — Implementation Status

> Updated by Phase 11 — Configuration and Ignore Controls (2026-08-17).

## Current Phase

**Phase 11 — Complete**

Next recommended phase: **Phase 12 — Database Compatibility Hardening** (await explicit maintainer instruction).

## Current State

The package ships finalized configuration for model discovery paths, ignored models, and table-scoped ignored columns. Ignore controls are applied predictably during discovery and analysis without baseline support.

### Phase 11 deliverables

- Documented `config/schema-contract.php` with `model_paths`, `ignore_models`, and table-keyed `ignore_columns`
- `IgnoreColumnMatcher` resolves table-specific column ignores from config (replaces global flattening)
- `ContractAnalyzer`, `CheckSchemaContractCommand`, and `AnalysisConsoleRenderer` use table-scoped ignores
- Ignored models excluded from discovery/bulk checks; FQCN targeting still works
- Ignored columns skipped only when the model's effective table matches the config key
- Unit tests for `IgnoreColumnMatcher`
- Feature tests for custom paths, ignored models, ignored columns, config overrides, and command integration

### Quality command results (Phase 11, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 41 files; `--memory-limit=512M` |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 257 tests, 684 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── Analysis/
│   └── ContractAnalyzer.php
├── Console/
│   ├── Commands/
│   │   └── CheckSchemaContractCommand.php
│   ├── Rendering/
│   │   └── AnalysisConsoleRenderer.php
│   └── Support/
│       └── ModelClassResolver.php
├── Discovery/
│   └── EloquentModelDiscoverer.php
├── Support/
│   └── IgnoreColumnMatcher.php
└── SchemaContractServiceProvider.php
```

### Configuration flow

```text
config/schema-contract.php
        ↓
model_paths / ignore_models → EloquentModelDiscoverer
ignore_columns (table-keyed)  → IgnoreColumnMatcher
        ↓
ContractAnalyzer + AnalysisConsoleRenderer
```

### Ignore behavior

| Setting | Scope | Effect |
|---|---|---|
| `model_paths` | Discovery | Directories scanned for concrete Eloquent models; falls back to `app/Models` when empty |
| `ignore_models` | Discovery | FQCNs excluded from bulk discovery; explicit FQCN argument still analyzable |
| `ignore_columns` | Analysis | Columns skipped per database table name during rule evaluation and console output |

## Dependencies

Unchanged from Phase 4.

## Testing State

| Layer | Status |
|---|---|
| Unit — ignore column matcher | `tests/Unit/Support/IgnoreColumnMatcherTest.php` |
| Feature — configuration | `tests/Feature/Configuration/SchemaContractConfigurationTest.php` |
| Feature — discovery | `tests/Feature/Discovery/EloquentModelDiscovererTest.php` |
| Feature — Artisan command | `tests/Feature/Commands/CheckSchemaContractCommandTest.php` |
| Integration — analyzer | `tests/Integration/Analysis/ContractAnalyzerTest.php` |

## CI State

Unchanged from prior phases.

## Risks

1. **Parallel Testbench flakiness** — occasional config bootstrap race on Windows under `--parallel`.
2. **No strict mode yet** — warnings do not fail CI by design for v0.1.0 default behavior.
3. **Table-keyed ignores only** — column ignores require the effective database table name as the config key.

## Conflicts With Master Specification

| Area | Status after Phase 11 |
|---|---|
| Config ignore finalization | Resolved |
| Baseline generation | Deferred — roadmap |
| JSON/GitHub output formats | Deferred — roadmap |

## Recommended Changes

### Phase 12 (when requested)

Harden SQLite/MySQL/MariaDB/PostgreSQL behavior and document verified driver support.

### Later phases

Phases 13–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–10 | Complete |
| 11 — Config / ignores | **Complete** |
| 12 — DB hardening | **Ready to begin** |
| 13–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 12)

Configuration and ignore controls are finalized, tested, and wired through discovery and analysis. Database compatibility hardening should not begin until Phase 12 is explicitly requested.
