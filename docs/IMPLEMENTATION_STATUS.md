# Laravel Schema Contract — Implementation Status

> Updated by Phase 16 — Release-Readiness Remediation (2026-08-17).

## Current Phase

**Phase 16 — Complete (conditional on GitHub Actions)**

Next recommended phase: **Phase 17 — Post-v0.1 Architecture Review** (await explicit maintainer instruction).

## Release-Readiness Verdict

**NOT RELEASE CANDIDATE READY until GitHub Actions is fully green**

Phase 16 remediation addressed the failing parallel Pest smoke job root cause and related CI packaging issues. Local validation passes, including serial and parallel Pest. Release readiness is **conditional on the next GitHub Actions run confirming all jobs pass**, especially `PHP 8.4 prefer-stable (parallel Pest smoke)`.

No tag, GitHub Release, Packagist publish, or branch merge was performed.

### GitHub Actions status (2026-08-17)

| Area | Status |
|---|---|
| PHP compatibility matrix (serial Pest) | Green (prior run) |
| Windows smoke | Green (prior run) |
| MySQL / PostgreSQL compatibility | Green (prior run) |
| Parallel Pest smoke | **Failed intermittently (remediated locally; await CI confirmation)** |
| `update-changelog.yml` | **Removed** — no post-release changelog mutation |

## Phase 16 remediation deliverables

### Test scoping

- `tests/Pest.php` now applies `Tests\TestCase` only to `Feature/` and `Integration/`
- Pure unit tests under `tests/Unit/` and architecture tests under `tests/Architecture/` run without Orchestra Testbench
- `IgnoreColumnMatcher` config integration tests moved from unit to `Feature/Configuration/`
- `ArchTest.php` relocated to `tests/Architecture/` for reliable Pest discovery in serial and parallel runs

### CI and packaging

- Composer cache in `tests.yml` and `database-compatibility.yml` caches download cache only (not `vendor/`)
- Fresh `composer update` + `composer run prepare` on each job avoids stale Testbench skeleton restores
- Deleted `.github/workflows/update-changelog.yml`
- Preserved `.gitattributes` export-ignore; `docs/DATABASE_SUPPORT.md` remains in the consumer archive

### Prior Phase 16 deliverables (retained)

- `CHANGELOG.md` with empty `[Unreleased]` above `[0.1.0] - 2026-08-17`
- `composer.json` support metadata and reliable `composer test` gate (serial Pest + `@prepare`)

## Definition of Done Audit (v0.1.0)

| Criterion | Result | Notes |
|---|---|---|
| Package behavior | Pass | Unchanged; audit confirmed |
| Local quality suite | Pass | See table below |
| CI fully green | **Pending** | Parallel smoke was red; remediation pushed |
| CHANGELOG ready | Pass | Manual Keep a Changelog; no auto-update workflow |
| No automatic release | Pass | No tag, release, or publish |

## Quality command results (Phase 16 remediation, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer check:composer` | Pass | |
| `composer analyse` | Pass | PHPStan L8, 44 files |
| `composer lint:check` | Pass | |
| `composer test:types` | Pass | 100% type coverage |
| `vendor/bin/pest` | Pass | 282 passed, 6 skipped (serial) |
| `vendor/bin/pest --parallel` | Pass | 282 passed, 6 skipped |
| `composer test` | Pass | Release gate |
| `composer test:unit` | Pass | Parallel |

## Package archive review

`git archive` export includes consumer documentation (`README.md`, `CHANGELOG.md`, `docs/DATABASE_SUPPORT.md`, `config/`, `src/`). Dev-only paths are excluded via `.gitattributes` (`tests/`, internal docs, workbench, CI configs).

## Parallel Pest boundary

After TestCase scoping, the **full suite** runs deterministically in parallel locally. The CI parallel smoke job continues to run `vendor/bin/pest --parallel` for the complete suite without `continue-on-error`. If CI still flakes, the boundary is Orchestra Testbench boot for Laravel-scoped tests only; pure unit/architecture tests no longer trigger Testbench during parallel workers.

## Known limitations (accepted for v0.1.0)

- MySQL/PostgreSQL grouped tests skip without driver env vars (6 tests)
- PostgreSQL native enums and extension types may map to `unknown`
- Roadmap features (validation, API Resources, Livewire, baselines, GitHub annotations) are not implemented

## Phase Readiness

| Phase | Status |
|---|---|
| 0–16 | Complete (CI confirmation pending) |
| 17 — Post-v0.1 review | Blocked — await maintainer instruction and green CI |

---

## Decision: **CONDITIONAL** (await green GitHub Actions)

Do not tag v0.1.0 until the remediated workflows pass on GitHub. Phase 17 should not begin until explicitly requested.
