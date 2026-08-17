# Laravel Schema Contract — Implementation Status

> Updated by Phase 10 — Artisan Command (2026-08-17).

## Current Phase

**Phase 10 — Complete**

Next recommended phase: **Phase 11 — Configuration and Ignore Controls** (await explicit maintainer instruction).

## Current State

The package ships **`php artisan schema-contract:check`** for discovering and analyzing Eloquent models with human-readable output, structured exit codes, and presentation kept separate from the analyzer core.

### Phase 10 deliverables

- `CheckSchemaContractCommand` (`schema-contract:check`)
- Discovers all configured models or analyzes a targeted model by FQCN / short class name
- `ModelClassResolver` with ambiguous/unresolvable model handling (exit code 2)
- `AnalysisConsoleRenderer` for per-model PASS/ERROR/WARNING output and application summary
- Exit codes: `0` = no blocking errors, `1` = contract errors, `2` = runtime/config/command failure
- Warnings alone do not fail the command by default
- Basic flattening of table-keyed `ignore_columns` config for analyzer use
- Feature tests for clean run, errors, warnings-only, no models, specific model, invalid input, ambiguous name, and exit codes
- Unit tests for `ModelClassResolver`

### Quality command results (Phase 10, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer lint:check` (Pint) | Pass | |
| `composer analyse` (PHPStan L7) | Pass | 40 files; `--memory-limit=512M` |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 244 tests, 650+ assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── Analysis/
│   └── ContractAnalyzer.php
├── Console/
│   ├── Commands/
│   │   └── CheckSchemaContractCommand.php
│   ├── Exceptions/
│   ├── Rendering/
│   │   └── AnalysisConsoleRenderer.php
│   └── Support/
│       └── ModelClassResolver.php
├── Discovery/
├── Inspectors/
├── Rules/
└── SchemaContractServiceProvider.php (registers command)
```

### Command flow

```text
schema-contract:check {model?}
        ↓
EloquentModelDiscoverer (+ optional ModelClassResolver)
        ↓
ContractAnalyzer::analyzeModels()
        ↓
AnalysisConsoleRenderer → summary + exit code
```

Rendering stays in the console layer; `ContractAnalyzer` remains presentation-independent.

### Exit code behavior

| Code | Meaning |
|---|---|
| 0 | No contract errors (warnings allowed) |
| 1 | One or more contract errors |
| 2 | Invalid/ambiguous model input or unexpected runtime failure |

## Dependencies

Unchanged from Phase 4.

## Testing State

| Layer | Status |
|---|---|
| Feature — Artisan command | `tests/Feature/Commands/CheckSchemaContractCommandTest.php` |
| Unit — model resolver | `tests/Unit/Console/ModelClassResolverTest.php` |
| Integration — analyzer | Phase 9 |
| Fixtures — command | `tests/Fixtures/Commands/` |

## CI State

Unchanged from prior phases.

## Risks

1. **Ignore columns flattening** — table-specific ignores are flattened globally until Phase 11 hardening.
2. **Parallel Testbench flakiness** — occasional config bootstrap race on Windows under `--parallel`.
3. **No strict mode yet** — warnings do not fail CI by design for v0.1.0 default behavior.

## Conflicts With Master Specification

| Area | Status after Phase 10 |
|---|---|
| Primary Artisan command | Resolved |
| Exit codes | Resolved |
| Targeted model analysis | Resolved |
| Config ignore finalization | Deferred — Phase 11 |
| JSON/GitHub output formats | Deferred — roadmap |

## Recommended Changes

### Phase 11 (when requested)

Finalize config-driven ignore controls and table-scoped column ignores.

### Later phases

Phases 12–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–9 | Complete |
| 10 — Artisan command | **Complete** |
| 11 — Config / ignores | **Ready to begin** |
| 12–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 11)

The primary command is implemented, tested, and separated from analyzer logic. Configuration hardening should not begin until Phase 11 is explicitly requested.
