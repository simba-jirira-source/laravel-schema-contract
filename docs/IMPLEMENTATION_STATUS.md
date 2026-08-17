# Laravel Schema Contract — Implementation Status

> Updated by Phase 1 — Package Foundation (2026-08-17).

## Current Phase

**Phase 1 — Complete**

Next recommended phase: **Phase 2 — Core Domain Types and DTOs** (await explicit maintainer instruction).

## Current State

The repository has a **clean Laravel 13+ / PHP 8.3+ package foundation** aligned with the master specification identity targets (namespace, provider, config). No schema-contract analysis functionality exists yet.

### Phase 1 deliverables

- Namespace migrated to `SimbaJirira\SchemaContract`
- `SchemaContractServiceProvider` with config merge and publish tag `schema-contract-config`
- Spec-aligned `config/schema-contract.php` (`model_paths`, `ignore_models`, `ignore_columns`)
- Laravel auto-discovery registered (no facade)
- Scaffold assets removed (placeholder command, facade, routes, views, translations, migrations, old config)
- Composer metadata tightened for Laravel 13+ / PHP 8.3+
- Unused scaffold dev dependencies removed (`laravel/agent-detector`, `laravel/chisel`, `laravel/pao`, `laravel/prompts`)

### Quality command results (Phase 1, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | Lock file regenerated |
| `composer lint:check` (Pint) | Pass | |
| `vendor/bin/pest` | Pass | 5 feature tests, 11 assertions |
| `composer test:unit` | Pass | 9 tests (feature + arch), 18 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
└── SchemaContractServiceProvider.php
```

### Config

```text
config/schema-contract.php
```

### Target layout (master spec §37 — Phases 2–9)

```text
src/
├── SchemaContractServiceProvider.php
├── Commands/
├── Contracts/
├── Analysis/
├── Discovery/
├── Inspectors/
├── DTO/
├── Enums/
├── Rules/
└── Support/
```

### Service provider behavior

`SchemaContractServiceProvider`:

- Merges `config/schema-contract.php` under the `schema-contract` key
- Publishes config with tag `schema-contract-config` (console only)
- Does not register commands, routes, views, translations, migrations, or container bindings beyond Laravel defaults

### Package auto-discovery

Configured in `composer.json` under `extra.laravel`:

- Provider: `SimbaJirira\SchemaContract\SchemaContractServiceProvider`
- No facade alias

## Dependencies

### Production (`composer.json`)

| Package | Constraint |
|---|---|
| `php` | `^8.3` |
| `illuminate/support` | `^13.0` |

### Development

| Package | Constraint |
|---|---|
| `orchestra/testbench` | `^11.0` |
| `pestphp/pest` + Laravel plugin | `^4.6\|\|^5.0` |
| `pestphp/pest-plugin-type-coverage` | `^4.0\|\|^5.0` |
| `laravel/pint` | `^1.29` |
| `larastan/larastan` | `^3.9` |
| `phpstan/extension-installer` | `^1.4` |

### Composer scripts

Unchanged from scaffold; `test:unit` runs Pest in parallel across all phpunit.xml.dist suites (Feature + Arch).

## Testing State

### Framework

- Pest 4/5 + Orchestra Testbench 11
- Architecture tests in `tests/ArchTest.php` (namespace updated to `SimbaJirira\SchemaContract`)

### Coverage

| Layer | Status |
|---|---|
| Feature | `tests/Feature/PackageFoundationTest.php` — boot, provider load, config merge, config override, config publish |
| Architecture | Strict types, security/php presets |
| Unit | None (placeholder removed) |
| Schema/model contract | None (Phase 2+) |

## CI State

`.github/workflows/tests.yml` still matrix-tests Laravel 12.* alongside 13.*. Phase 1 raised the package minimum to Laravel 13+; CI alignment is deferred to Phase 14.

Other gaps unchanged from Phase 0: no `composer validate` step, no `development` branch push trigger, no database matrix.

## Risks

1. **CI / composer constraint mismatch** — Workflow still installs Laravel 12; package now requires `^13.0`. CI may fail or need matrix update in Phase 14.
2. **Composer package name** — Packagist name remains `simba-jirira-source/laravel-schema-contract`; master spec references `simba-jirira/laravel-schema-contract`. Intentional per workspace conventions until maintainer decides otherwise.
3. **Premature release signals** — README and CHANGELOG still describe unreleased v0.1.0 (Phase 15/16).
4. **No Artisan command yet** — `schema-contract:check` deferred to Phase 10; no CLI entry point registered.

## Conflicts With Master Specification

| Area | Status after Phase 1 |
|---|---|
| Namespace `SimbaJirira\SchemaContract` | Resolved |
| Provider `SchemaContractServiceProvider` | Resolved |
| Config `schema-contract.php` with model/ignore keys | Resolved |
| Facade removed | Resolved |
| Scaffold bloat removed | Resolved |
| Composer package `simba-jirira/laravel-schema-contract` | Open — using `simba-jirira-source/…` |
| Primary command `schema-contract:check` | Deferred — Phase 10 |
| Core analysis architecture | Not started — Phase 2+ |
| README / CHANGELOG accuracy | Open — Phase 15/16 |
| CI Laravel 13+ alignment | Open — Phase 14 |

## Recommended Changes

### Phase 2 (when requested)

Add typed domain model: `DatabaseType`, `CastType`, `Severity`, `ModelDefinition`, `TableDefinition`, `ColumnDefinition`, `CastDefinition`, `ContractViolation`.

### Later phases

Unchanged from implementation plan (Phases 3–17).

## Phase Readiness

| Phase | Status |
|---|---|
| 0 — Discovery audit | Complete |
| 1 — Package foundation | **Complete** |
| 2 — Core domain types and DTOs | **Ready to begin** |
| 3–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 2)

Phase 1 foundation is in place. The package boots in Testbench, auto-discovers correctly, merges and publishes configuration, and passes validation, Pint, and Pest.

**Phase 2 may proceed** once explicitly requested. Do not implement DTOs or analysis until then.
