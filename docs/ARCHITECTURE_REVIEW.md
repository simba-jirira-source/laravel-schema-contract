# Post-v0.1 Architecture Review

> Phase 17 — 2026-08-17. Review target: v0.1.0 release-ready codebase (commit `940b396` verified green in CI).

This document records architecture findings after v0.1.0 completion. It does **not** implement v0.2 functionality.

## Executive summary

The v0.1 architecture delivers a clean separation between discovery, inspection, normalization, compatibility, rules, analysis, and console rendering. Layer boundaries are enforced by architecture tests. The design is sufficient for **Database ↔ Eloquent** analysis and CI usage.

The main gaps before expanding to **Database ↔ Validation** are:

1. No validation-layer inspection boundary or DTOs yet.
2. Extension points exist in code (`RuleRegistry::register`) but are not container-backed or documented for consumers.
3. Per-run schema metadata caching (master spec §31) is not implemented — duplicate table reads are possible when multiple models share a table/connection.
4. Output is human-console only; structured JSON/GitHub formats remain roadmap items.

None of these block v0.1.0 release. Items marked **required before v0.2** should be addressed in the first v0.2 implementation phases.

---

## Current architecture (v0.1)

```text
schema-contract:check
        │
        ▼
EloquentModelDiscoverer ──► list<model FQCN>
        │
        ▼
ContractAnalyzer (per model)
        ├── EloquentModelInspector ──► ModelDefinition
        ├── EloquentSchemaInspector ──► TableDefinition
        └── RuleRegistry ──► list<ContractViolation>
                │
                ▼
        AnalysisResult + AnalysisSummary
                │
                ▼
AnalysisConsoleRenderer (CLI only)
```

**Strengths**

| Area | Assessment |
|---|---|
| Layer separation | Analysis, rules, compatibility, and console are isolated; arch tests enforce boundaries |
| DTOs | Readonly value objects with typed enums; suitable foundation for additional layers |
| Normalization | Driver-specific logic contained in `Support\Database\*` and `ColumnTypeParser` |
| False-positive policy | Unknown types, missing decimal metadata, and standard timestamps handled conservatively |
| Testability | Pure unit tests run without Testbench; integration coverage for drivers and commands |
| Configuration | Minimal surface (`model_paths`, `ignore_models`, `ignore_columns`) |

---

## Rule API

**Current state**

- `ContractRule` interface: `identifier()` + `analyze(ModelDefinition, ColumnDefinition)`.
- `RuleRegistry` holds ordered rules; `withDefaults()` registers four built-in rules; `register()` appends custom rules.
- Rules delegate to `TypeCompatibilityMatrix` or specialized logic; violations flow through `ViolationFactory`.
- `CastMatchesColumnTypeRule` defers JSON, decimal-scale, and date/time columns to specialized rules — avoids duplicate violations.

**Findings**

- Rule API is **column-scoped** and **model-scoped**. Validation analysis will need **field/rule-scoped** concepts (FormRequest attributes, rule strings, nullable/required semantics).
- `RuleRegistry` is instantiated inside the command (`RuleRegistry::withDefaults()`), not resolved from the container. Custom rules require manual `ContractAnalyzer` construction today.
- No deduplication if two rules emit the same violation; order-dependent output only.
- Rule identifiers are string constants — good for future baselines/suppressions (v0.8 roadmap).

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Introduce a parallel rule/inspector concept for **validation contracts** rather than overloading `ContractRule` with non-column semantics |
| Required before v0.2 | Define how validation rules map to `ColumnDefinition` / table columns (direct column, nested array keys, conditional rules) |
| Desirable before v1.0 | Bind `RuleRegistry` (or an analyzer factory) in the service provider; document consumer override pattern |
| Desirable before v1.0 | Add optional rule deduplication or stable violation identity for baseline support |
| Future / nice-to-have | Public `SchemaContract::extend(ContractRule)` facade-style API per master spec roadmap |

---

## DTO stability

**Current state**

- Core DTOs: `ModelDefinition`, `TableDefinition`, `ColumnDefinition`, `CastDefinition`, `ContractViolation`, `ContractResult`, `AnalysisResult`, `AnalysisSummary`, `CompatibilityResult`.
- All DTOs under `DTO/` are `readonly` classes with typed properties.
- `ContractViolation` carries optional metadata (suggested cast, precision/scale) for rendering and future formatters.

**Findings**

- DTO set is **stable for v0.1 public programmatic use** via `ContractAnalyzer` return types.
- v0.2 will require **new DTOs** (e.g. validation field definition, validation rule expression, cross-layer violation linking). Avoid extending `ContractViolation` with validation-only fields without namespacing or layer tags.
- `ModelDefinition::casts` is keyed by column name only; accessor/mutator-only virtual attributes are out of scope for v0.1 (correct).

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Add validation-specific DTOs in a dedicated namespace or subdirectory; do not reuse `ColumnDefinition` for non-column validation keys without explicit mapping |
| Desirable before v1.0 | Document which DTOs are considered public API vs internal |
| Future / nice-to-have | Versioned analysis result schema for JSON output (v0.7) |

---

## Extension points

**Current state**

- Interfaces: `ModelDiscoverer`, `ModelInspector`, `SchemaInspector`, `ContractRule`.
- Concrete defaults wired via constructor defaults on `ContractAnalyzer`.
- Service provider registers config and command only — **no container bindings** for analyzers or rules.
- No facade, no published extension documentation beyond README programmatic note.

**Findings**

- Extension is **possible but manual** — consumers must instantiate `ContractAnalyzer` with custom dependencies.
- Discovery is hard-coded to `EloquentModelDiscoverer` in the command; validation discovery (FormRequest classes, rule arrays) will need parallel discoverers.
- `ignore_models` applies at discovery time; targeted analysis by FQCN still inspects ignored models (documented behavior — acceptable).

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Add `ValidationDiscoverer` / `ValidationInspector` interfaces (names TBD) before implementing validation rules |
| Desirable before v1.0 | Register default analyzer graph in service provider; allow config-driven rule registration |
| Future / nice-to-have | Documented extension API (v0.9 roadmap) |

---

## Database support

**Current state**

- SQLite, MySQL/MariaDB, PostgreSQL via Laravel `Schema::getColumns()`.
- Driver enrichment in `DriverColumnMetadataEnricher`; conservative `Unknown` degradation.
- CI verifies SQLite (full suite), MySQL/PostgreSQL (grouped integration).
- Limitations documented in `docs/DATABASE_SUPPORT.md`.

**Findings**

- PostgreSQL native enums and extension types remain partially verified — acceptable for v0.1.
- SQLite integer size collapse is a metadata limitation, not a logic bug.
- Validation layer (v0.2) may introduce **rule-string-to-type** mapping independent of driver metadata; reuse `DatabaseType` where possible.

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | None blocking — validation compares against existing `ColumnDefinition` metadata |
| Desirable before v1.0 | Expand PostgreSQL enum/UDT verification; document MariaDB-specific edge cases if reported |
| Future / nice-to-have | SQL Server / Oracle only if product scope expands |

---

## False-positive risk

**Current state**

- `ERROR` reserved for high-confidence incompatibility (type mismatch, scale mismatch when metadata exists, missing table).
- `WARNING` for JSON without cast, uncertain unknown types with suggestions.
- `INFO` largely suppressed at rule level for uncertain compatible states.
- Standard `created_at` / `updated_at` excluded from date/time warnings.
- String/text/uuid columns without cast treated as compatible.

**Findings**

- `deleted_at` is **not** in the standard timestamp exclusion list — may warn if datetime column lacks cast (Laravel often uses datetime cast or none). Monitor in real apps; may need exclusion or cast inference.
- Custom casts always skip strict matrix comparison — correct for v0.1 but may hide mismatches if custom cast semantics differ.
- Multiple models on one table analyzed independently — consistent but may repeat warnings per model.

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Define false-positive policy for **validation vs schema** (e.g. `nullable` vs `required` — high false-positive risk if inferred incorrectly) |
| Desirable before v1.0 | Consider `deleted_at` alongside standard timestamps; configurable timestamp column list |
| Future / nice-to-have | Per-project baseline/suppression (v0.8) |

---

## Performance

**Current state**

- Each model triggers fresh `EloquentModelInspector` work (instantiates model, reads casts).
- Each model triggers `EloquentSchemaInspector::inspect()` — **no cross-model table cache**.
- Discovery scans filesystem with regex class extraction on every run.
- Single-threaded analysis; no persistent cache.

**Findings**

- Master spec §31 recommends caching schema metadata within a run — **not yet implemented**. Impact is low for typical app model counts but grows with shared tables and large model sets.
- Validation layer (v0.2) adds FormRequest/reflection work — table and model caching becomes more valuable.

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Optional but recommended: **table-level cache** keyed by `connection.table` inside `ContractAnalyzer` or `EloquentSchemaInspector` |
| Desirable before v1.0 | Cache `ModelDefinition` per FQCN within a run; reduce duplicate model booting |
| Future / nice-to-have | Composer-based class discovery instead of regex file scanning; persistent analysis cache |

---

## Developer experience

**Current state**

- Install: `composer require simba-jirira-source/laravel-schema-contract --dev`
- Command: `php artisan schema-contract:check` with optional model argument and short-name resolution.
- Exit codes 0/1/2 documented; warnings do not fail CI.
- README, DATABASE_SUPPORT, Boost skill cover adoption.

**Findings**

- No `--strict` (fail on warnings), `--json`, or `--format=github` — correctly deferred.
- Command constructs analyzer inline — consumers cannot swap dependencies via config alone.
- Empty discovery exits 0 with warning — good for greenfield apps, may surprise teams expecting failure.

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Document validation-analysis usage patterns once implemented; keep CLI flags minimal until v0.7 |
| Desirable before v1.0 | `--strict` option; structured JSON output for CI consumers |
| Future / nice-to-have | GitHub annotation format (v0.7); IDE integration |

---

## Command output

**Current state**

- `AnalysisConsoleRenderer` renders per-model blocks, PASS/ERROR/WARNING lines, summary counts.
- Rendering respects `ignore_columns` (skipped columns omitted from output).
- Missing table renders table-level violation without column detail.

**Findings**

- Output format is stable and tested via feature tests.
- Renderer is the only consumer of `ContractResult` today — good separation.
- v0.2 may need multi-layer sections (Schema vs Validation) or unified violation list with layer tags.

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Add `layer` or `source` field to `ContractViolation` (or parallel DTO) to distinguish schema vs validation findings in output |
| Desirable before v1.0 | Extract rendering behind an interface for JSON/console implementations |
| Future / nice-to-have | GitHub-annotated output |

---

## Configuration

**Current state**

- `model_paths`, `ignore_models`, `ignore_columns` only.
- No env-based overrides, no per-connection settings, no rule toggles.

**Findings**

- Appropriate minimalism for v0.1.
- v0.2 will need paths for FormRequests or validation discovery roots; avoid overloading `model_paths`.

**Recommendations**

| Priority | Recommendation |
|---|---|
| Required before v0.2 | Add dedicated config keys for validation discovery (e.g. `request_paths` or `validation_paths`) — do not reuse `model_paths` |
| Desirable before v1.0 | Optional rule enable/disable map; configurable standard timestamp columns |
| Future / nice-to-have | Baseline file path (v0.8) |

---

## Technical debt (internal)

| Item | Severity | Notes |
|---|---|---|
| Command builds `ContractAnalyzer` manually | Low | Bypasses container; limits extension |
| No service provider bindings | Low | Acceptable for v0.1 |
| Regex-based model discovery | Medium | Edge cases: multiple classes per file, enum classes filtered correctly |
| No in-run table cache | Medium | Master spec gap; easy win before v0.2 load increases |
| `TypeCompatibilityMatrix` instantiated per rule | Low | Stateless; negligible cost |
| Testbench prepare required for Laravel tests | Low | Mitigated by Pest scoping (Phase 16) |
| `RuleRegistry` not immutable after register | Low | Document or return new instance if cloning needed |

No **required before v0.1 release** debt remains. Items above inform v0.2+ planning.

---

## Recommendation summary

### Required before v0.2

1. Design validation inspection boundary (discoverer, inspector, DTOs, rule mapping to columns).
2. Extend violation/result model with layer identification for schema vs validation.
3. Define false-positive policy for validation comparisons (required/nullable/type rules).
4. Add validation-specific config (discovery paths separate from `model_paths`).
5. Consider in-run table metadata cache before adding validation workload.

### Desirable before v1.0

1. Service provider bindings and documented custom rule registration.
2. `--strict` and JSON output architecture.
3. Public DTO/API stability documentation.
4. Expanded driver verification (PostgreSQL enums/UDTs).
5. Configurable standard timestamp columns (`deleted_at`).
6. Renderer abstraction for multiple output formats.

### Future / nice-to-have

1. `SchemaContract::extend()` public API.
2. Baselines and suppressions.
3. GitHub annotation output.
4. Composer-native discovery; persistent caches.
5. Additional database drivers.

---

## Recommended next step toward v0.2.0

**Begin v0.2 planning and Phase 1 of Database ↔ Validation implementation** (maintainer instruction required):

1. **Specification slice** — Define v0.2 scope: which validation sources (FormRequest only vs inline `$rules` arrays), which rule types (`string`, `integer`, `date`, `array`, `exists`, etc.), and severity policy.
2. **Architecture spike** — Add validation DTOs and interfaces without wiring CLI yet; prove one rule (e.g. `required|string` column vs `varchar` nullable).
3. **Extend analyzer orchestration** — Either extend `ContractAnalyzer` with a validation phase or introduce a composable pipeline; preserve presentation independence.
4. **Tests first** — Unit tests for validation rule normalization; feature tests for a single FormRequest ↔ schema check.

Do not implement full validation analysis until the maintainer explicitly requests the v0.2 phase.
