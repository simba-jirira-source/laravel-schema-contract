# Laravel Schema Contract — Implementation Status

> Updated by Phase 14 — CI (2026-08-17).

## Current Phase

**Phase 14 — Complete**

Next recommended phase: **Phase 15 — README and Documentation** (await explicit maintainer instruction).

## Current State

GitHub Actions now validate pull requests and pushes to `main` and `development` with Composer validation, PHPStan L8 + Larastan, Pint, Pest, type coverage, and a practical PHP/Laravel 13 matrix. MySQL and PostgreSQL driver integration tests run in a separate proportionate workflow.

### Phase 14 deliverables

- Refined `.github/workflows/tests.yml` for PRs, `main`, and `development`
- Ubuntu matrix: PHP 8.3/8.4/8.5 × prefer-lowest/prefer-stable with full quality pipeline
- Windows smoke job: PHP 8.4, non-parallel Pest (avoids known parallel Testbench flakiness)
- Composer dependency caching on all CI jobs (`vendor` + Composer cache directory)
- Explicit `composer check:composer`, `composer analyse`, `composer lint:check`, `composer test:types`, and `composer test:unit` steps with failing exit codes
- Refined `.github/workflows/database-compatibility.yml` with caching and Composer validation
- Removed outdated Laravel 12 matrix entries (package targets Laravel 13+ per `composer.json` and master spec)
- Workflow YAML syntax validated locally

### CI workflows

| Workflow | Triggers | Jobs |
|---|---|---|
| `tests.yml` | PR, `main`, `development` | 6 Ubuntu matrix jobs + 1 Windows smoke job |
| `database-compatibility.yml` | PR, `main`, `development` | MySQL + PostgreSQL integration groups |
| `update-changelog.yml` | Release published | Unchanged (no auto-publish) |

### Quality command results (Phase 14, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | via `composer check:composer` |
| `composer analyse` (PHPStan L8 + Larastan) | Pass | 44 files |
| `composer lint:check` (Pint) | Pass | |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 279 passed, 6 skipped, 734 assertions |
| Workflow YAML validation | Pass | local Python `yaml.safe_load` |

## Existing Architecture

Unchanged from Phase 13.

## Dependencies

Unchanged from Phase 13.

## Testing State

Unchanged from Phase 13. CI now executes the full local quality pipeline on every matrix lane.

## CI State

| Check | Ubuntu matrix | Windows smoke | DB workflow |
|---|---|---|---|
| Composer validate | Yes | Yes | Yes |
| PHPStan L8 + Larastan | Yes | Yes | No |
| Pint | Yes | Yes | No |
| Type coverage | Yes | No | No |
| Pest (full suite) | Yes (parallel) | Yes (serial) | Grouped driver tests only |

## Risks

1. **Windows parallel Pest** — excluded from Windows job; Ubuntu covers parallel execution.
2. **prefer-lowest matrix** — may surface dependency edge cases intentionally.
3. **DB workflow scope** — verifies grouped integration tests only, not the full suite against live databases.

## Conflicts With Master Specification

None identified for Phase 14 scope. No tags, releases, or Packagist publication were added.

## Recommended Changes

### Phase 15 (when requested)

Document v0.1 functionality in README, including CI usage and supported environments.

### Later phases

Phases 16–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–13 | Complete |
| 14 — CI | **Complete** |
| 15 — README / docs | **Ready to begin** |
| 16–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 15)

CI validates the package on pull requests and primary branches. Documentation work should not begin until Phase 15 is explicitly requested.
