# Laravel Schema Contract — Implementation Status

> Updated by Phase 0 — Repository Discovery and Architecture Audit (2026-08-17).

## Current Phase

**Phase 0 — Complete**

Next recommended phase: **Phase 1 — Package Foundation** (await explicit maintainer instruction).

## Current State

The repository is a **fresh Laravel package scaffold** with a single committed baseline (`chore: initialise Laravel Schema Contract package`) on the `development` branch. Remote branches `main` and `development` exist.

**No schema-contract analysis functionality is implemented.** The package currently ships boilerplate only:

- Empty `LaravelSchemaContract` singleton
- Placeholder Artisan command (`laravel-schema-contract:placeholder`)
- Placeholder config, translation, view, route, and migration assets
- Example Pest tests asserting scaffold wiring

The **Cursor development pack has been installed at the repository root**: `docs/` (spec files) and `.cursor/rules/schema-contract.mdc` are present. The former `dev-pack/` content appears migrated to root (uncommitted: new `docs/`, `.cursor/`; deleted `dev-pack/docs/` and `dev-pack/.cursor/`).

### Quality command results (local, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate` | Partial pass | `composer.json` valid; lock file out of date with `composer.json` |
| `composer lint:check` (Pint) | Pass | |
| `vendor/bin/phpstan analyse` | Pass | Level 7; elevated memory recommended locally (`512M`) |
| `vendor/bin/pest` | Pass | 7 tests, 8 assertions |

## Existing Architecture

### Source layout (actual)

```text
src/
├── LaravelSchemaContractServiceProvider.php
├── LaravelSchemaContract.php
├── Console/Commands/LaravelSchemaContractCommand.php
└── Facades/LaravelSchemaContract.php
```

### Supporting scaffold assets (not required by v0.1 spec)

```text
config/laravel-schema-contract.php
database/migrations/…_placeholder_table.php
lang/en/messages.php
resources/views/placeholder.blade.php
routes/laravel-schema-contract.php
public/                          (empty)
workbench/                       (Testbench app skeleton)
resources/boost/skills/…         (placeholder Boost skill)
```

### Target layout (master spec §37 — not yet created)

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

`LaravelSchemaContractServiceProvider` currently:

- Merges and publishes `config/laravel-schema-contract.php`
- Loads routes, views, and translations
- Publishes views, translations, public assets, and migrations
- Registers the placeholder console command
- Binds an empty `LaravelSchemaContract` singleton

None of the analysis, discovery, inspection, rule, or rendering components described in the master spec exist.

### Package auto-discovery

Configured in `composer.json` under `extra.laravel`:

- Provider: `LaravelSchemaContract\LaravelSchemaContract\LaravelSchemaContractServiceProvider`
- Facade alias: `LaravelSchemaContract` → `LaravelSchemaContract\LaravelSchemaContract\Facades\LaravelSchemaContract`

Testbench discovery confirms the package registers successfully.

## Dependencies

### Production (`composer.json`)

| Package | Constraint | Spec alignment |
|---|---|---|
| `php` | `^8.3` | Meets PHP 8.3+ target |
| `illuminate/support` | `^12.0\|\|^13.0` | Spec minimum is Laravel 13+; constraint also allows Laravel 12 |

Production dependencies are minimal (appropriate for the product direction).

### Development

| Package | Purpose | Notes |
|---|---|---|
| `orchestra/testbench` | `^10.0\|\|^11.0` | Matches Laravel 12/13 matrix |
| `pestphp/pest` + Laravel plugin | Testing | Configured |
| `pestphp/pest-plugin-type-coverage` | 100% type coverage gate | Enforced in `composer test` |
| `laravel/pint` | Code style | Configured (`pint.json`, Laravel preset) |
| `larastan/larastan` + `phpstan/extension-installer` | Static analysis | Level 7 (`phpstan.neon.dist`) |
| `laravel/agent-detector`, `laravel/chisel`, `laravel/pao`, `laravel/prompts` | Scaffold extras | Not required by master spec; review in Phase 1 |

### Composer scripts

```text
prepare     → testbench package:discover
build       → testbench workbench:build
serve       → build + testbench serve
lint        → pint
lint:check  → pint --test
analyse     → phpstan analyse
test:types  → pest --type-coverage --min=100
test:unit   → pest --parallel
test        → analyse + lint:check + test:types + test:unit
```

## Testing State

### Framework

- **Pest 4/5** with `pestphp/pest-plugin-laravel`
- **Orchestra Testbench** via `tests/TestCase.php`
- **Architecture tests** in `tests/ArchTest.php` (PHP/security presets, strict types)

### Coverage today

| Layer | Status |
|---|---|
| Unit | Placeholder only (`tests/Unit/ExampleTest.php`) |
| Feature | Scaffold wiring (`tests/Feature/ExampleTest.php`) — singleton, config merge, translations, views, command |
| Integration | None |
| Schema/model contract tests | None |

### Test observations

- Feature tests validate scaffold resources (views, translations, migrations publish tags) that v0.1 read-only analysis tooling does not need long term.
- No database-driver or schema-inspection tests exist (expected pre-implementation).
- CI runs parallel Pest on non-Windows; sequential Pest on Windows.

## CI State

### Workflow: `.github/workflows/tests.yml`

**Triggers:** push to `main`, `*.x`; pull requests. Does **not** trigger on direct push to `development` (only via PR).

**Matrix:**

- OS: `ubuntu-latest`, `windows-latest`
- PHP: 8.3, 8.4, 8.5
- Laravel: 12.*, 13.*
- Stability: prefer-lowest, prefer-stable
- Testbench: 10.* (L12), 11.* (L13)

**Steps:** checkout → setup PHP → `composer update` with matrix constraints → `composer run prepare` → PHPStan → Pint → type coverage (non-Windows) → Pest.

**Gaps vs master spec / implementation plan (Phase 14 targets):**

| Area | Current | Target |
|---|---|---|
| Branch coverage | `main`, `*.x`, PRs | Also validate `development` |
| `composer validate` | Not run | Should run |
| Database matrix | None | SQLite default; MySQL/PostgreSQL where practical |

### Other GitHub config

- `update-changelog.yml` — updates CHANGELOG on GitHub release
- `dependabot.yml` — weekly GitHub Actions and Composer updates
- `CONTRIBUTING.md`, `SECURITY.md`, issue templates — present
- `release.yml` listed in tree but not readable at audit time (may be absent or inaccessible)

## Risks

1. **Identity drift** — Composer name, namespace, command, and config file names differ from the master spec and Cursor rules. Phase 1 must reconcile deliberately (note: workspace `AGENTS.md` references `simba-jirira-source`, while master spec references `simba-jirira`).
2. **Scaffold surface area** — Routes, views, translations, migrations, assets, and facade add maintenance burden unrelated to read-only schema analysis.
3. **Premature release signals** — `CHANGELOG.md` already lists `[v0.1.0]` with placeholder date; README badges imply a published product.
4. **Uncommitted dev-pack migration** — Spec docs and Cursor rules moved to root but not yet committed; `dev-pack/` deletions are unstaged.
5. **Lock file stale** — `composer validate` reports lock/json mismatch; CI always runs `composer update`, masking local lock drift.
6. **PHPStan memory** — Full `composer test` may fail locally at default 128M PHP memory; CI may succeed with different limits.
7. **Laravel 12 support** — CI tests Laravel 12; spec targets 13+ minimum. Supporting 12 is a compatibility choice that should be explicit in Phase 1/14.

## Conflicts With Master Specification

| Area | Master spec / rules | Repository today | Severity |
|---|---|---|---|
| Composer package | `simba-jirira/laravel-schema-contract` | `simba-jirira-source/laravel-schema-contract` | Medium — align in Phase 1 (confirm org naming) |
| Namespace | `SimbaJirira\SchemaContract` | `LaravelSchemaContract\LaravelSchemaContract` | High — Phase 1 |
| Service provider | `SchemaContractServiceProvider` | `LaravelSchemaContractServiceProvider` | High — Phase 1 |
| Primary command | `schema-contract:check` | `laravel-schema-contract:placeholder` | High — Phase 10 (rename stub in Phase 1) |
| Config file/key | `config/schema-contract.php` | `config/laravel-schema-contract.php` | High — Phase 1/11 |
| Config contents | `model_paths`, `ignore_models`, `ignore_columns` | `placeholder => default` | High — Phase 11 |
| Facade | Optional; avoid without benefit | Registered with alias | Low — remove or defer in Phase 1 |
| Core architecture | Discovery, inspectors, DTOs, rules, analyzer | None | Expected — Phases 2–9 |
| Analysis output / exit codes | Structured contract results, 0/1/2 | Placeholder line, always 0 | Expected — Phase 10 |
| Read-only tooling | No schema/model mutation | Scaffold migration published | Medium — remove unused publishables |
| Documentation | Describe implemented analysis only | README documents scaffold publish tags | Medium — Phase 15 |
| CHANGELOG format | Keep a Changelog, empty `[Unreleased]` | Titled "Release Notes"; `[v0.1.0]` pre-dated | Low — Phase 16 |
| CI branch `development` | Validate PRs and development | Push trigger excludes `development` | Low — Phase 14 |

## Recommended Changes

### Phase 1 (Package Foundation) — when explicitly requested

1. **Reconcile package identity** — namespace, provider class name, config filename, and command signature toward spec targets; confirm whether Packagist name stays `simba-jirira-source/…` or moves to `simba-jirira/…`.
2. **Remove or defer scaffold assets** not needed for v0.1 read-only analysis: routes, views, translations, public assets, placeholder migration, facade (unless explicitly retained).
3. **Replace placeholder config** structure with minimal spec-aligned keys (values can remain empty until Phase 11).
4. **Regenerate `composer.lock`** and ensure `composer validate` passes cleanly.
5. **Address PHPStan memory** in CI/local scripts if `composer test` fails at default memory limits.
6. **Refresh feature tests** to assert foundation wiring only (provider, config merge, command registration) without views/translations/migrations unless retained.
7. **Commit dev-pack migration** — staged `docs/`, `.cursor/rules/`, and removal of redundant `dev-pack/` copies.

### Subsequent phases (do not start without instruction)

| Phase | Focus |
|---|---|
| 2 | Core domain types and DTOs |
| 3–4 | Database and cast normalization |
| 5–6 | Model discovery and schema inspection |
| 7–8 | Compatibility engine and contract rules |
| 9–10 | Analyzer and `schema-contract:check` command |
| 11–12 | Config/ignores and DB driver hardening |
| 13–14 | Static analysis quality and CI matrix |
| 15–16 | Documentation and release-readiness audit |

## Phase Readiness

| Phase | Status | Notes |
|---|---|---|
| 0 — Discovery audit | **Complete** | This document |
| 1 — Package foundation | **Ready to begin** | Scaffold is installable; tooling present; identity cleanup required |
| 2–17 | **Blocked** | Await maintainer instruction after Phase 1 |

---

## Decision: **READY**

Phase 0 is complete. The repository is a valid, testable Laravel package scaffold with Pest, Pint, PHPStan, and Testbench already wired. Spec documents and Cursor rules are installed at the repository root. No schema-contract product code exists yet, which is expected.

**Phase 1 may proceed** once the maintainer explicitly requests it. The main work is reconciling package identity and trimming scaffold assets that conflict with the read-only analysis architecture.

**Not BLOCKED** because:

- PHP/Laravel version constraints meet or exceed minimum targets
- Auto-discovery and Testbench integration work
- Tests pass
- Static analysis and Pint pass
- CI workflow exists and covers a broad PHP/Laravel matrix
- Specification and phased plan are available at `docs/`

**Caveats for Phase 1:**

- Resolve naming conflicts deliberately (`simba-jirira` vs `simba-jirira-source`)
- Do not implement analyzer logic in Phase 1
- Commit the dev-pack-to-root migration when ready
