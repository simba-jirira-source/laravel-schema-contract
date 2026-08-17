# Laravel Schema Contract — Implementation Status

> Updated by Phase 14 — CI remediation (2026-08-17).

## Current Phase

**Phase 14 — Complete (pending GitHub Actions verification)**

Next recommended phase: **Phase 15 — README and Documentation** (await explicit maintainer instruction).

## Current State

GitHub Actions validate pull requests and pushes to `main` and `development`. Database compatibility jobs for MySQL and PostgreSQL pass in CI. The main tests workflow was remediated for prefer-lowest PHPStan compatibility, reliable serial Pest execution in the compatibility matrix, valid cache action pins, and a dedicated parallel Pest smoke job.

### Phase 14 deliverables

- Refined `.github/workflows/tests.yml` for PRs, `main`, and `development`
- Ubuntu compatibility matrix: PHP 8.3/8.4/8.5 × prefer-lowest/prefer-stable with full quality pipeline
- Serial Pest execution (`vendor/bin/pest`) in the primary compatibility matrix for Testbench reliability
- Dedicated Ubuntu PHP 8.4 prefer-stable parallel Pest smoke job (`vendor/bin/pest --parallel`)
- Windows smoke job: PHP 8.4, serial Pest
- Composer dependency caching via `actions/cache@v6.1.0` on all CI jobs
- Explicit `composer check:composer`, `composer analyse`, `composer lint:check`, `composer test:types`, and full Pest suite on matrix lanes
- `.github/workflows/database-compatibility.yml` with caching, Composer validation, and grouped MySQL/PostgreSQL integration tests
- Removed outdated Laravel 12 matrix entries (package targets Laravel 13+ per `composer.json` and master spec)

### Phase 14 remediation (2026-08-17)

| Issue | Resolution |
|---|---|
| Invalid `actions/cache` commit SHA | Pinned to `actions/cache@v6.1.0` (`55cc8345863c7cc4c66a329aec7e433d2d1c52a9`) |
| MySQL compatibility failures | Verified passing in GitHub Actions |
| PostgreSQL compatibility failures | Verified passing in GitHub Actions; timestamp-with-time-zone parsing fixed in `ColumnTypeParser` |
| prefer-lowest PHPStan failure in `CastNormalizer` | Simplified enum detection to `enum_exists($class)` only |
| Parallel Pest/Testbench config race in matrix | Primary matrix uses serial Pest; parallel coverage moved to dedicated smoke job |
| Node.js 20 deprecation warning from cache action | Resolved by upgrading to `actions/cache@v6.1.0` |

### CI workflows

| Workflow | Triggers | Jobs |
|---|---|---|
| `tests.yml` | PR, `main`, `development` | 6 Ubuntu matrix jobs + 1 parallel smoke job + 1 Windows smoke job |
| `database-compatibility.yml` | PR, `main`, `development` | MySQL + PostgreSQL integration groups |
| `update-changelog.yml` | Release published | Unchanged (no auto-publish) |

### Quality command results (Phase 14 remediation, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer check:composer` | Pass | |
| `composer analyse` (PHPStan L8 + Larastan) | Pass | 44 files |
| `composer lint:check` (Pint) | Pass | |
| `composer test:types` | Pass | 100% type coverage |
| `vendor/bin/pest` | Pass | 271 passed, 6 skipped, 712 assertions (serial) |
| `composer test:unit` | Pass | 282 passed, 6 skipped, 737 assertions (parallel) |
| Workflow YAML validation | Pass | local Python `yaml.safe_load` |

### GitHub Actions verification status

| Workflow / area | Local expectation | CI status |
|---|---|---|
| `database-compatibility.yml` (MySQL) | Pass when driver configured | Verified passing |
| `database-compatibility.yml` (PostgreSQL) | Pass when driver configured | Verified passing |
| `tests.yml` compatibility matrix | Pass after remediation | Expected passing; await next CI run |
| `tests.yml` parallel smoke | Pass locally with `--parallel` | Expected passing; await next CI run |

## Existing Architecture

Unchanged from Phase 13.

## Dependencies

Unchanged from Phase 13.

## Testing State

Unchanged from Phase 13. CI executes the full local quality pipeline on every compatibility matrix lane.

## CI State

| Check | Ubuntu matrix | Parallel smoke | Windows smoke | DB workflow |
|---|---|---|---|---|
| Composer validate | Yes | Yes | Yes | Yes |
| PHPStan L8 + Larastan | Yes | No | Yes | No |
| Pint | Yes | No | Yes | No |
| Type coverage | Yes | No | No | No |
| Pest (full suite) | Yes (serial) | Yes (parallel) | Yes (serial) | Grouped driver tests only |

## Risks

1. **Parallel Pest smoke job** — isolated from the main matrix but may still surface Testbench race conditions on future dependency changes.
2. **prefer-lowest matrix** — intentionally surfaces dependency edge cases.
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
| 14 — CI | **Complete (await final GitHub Actions confirmation)** |
| 15 — README / docs | **Ready to begin** |
| 16–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 15 after CI confirmation)

CI remediation is complete locally. Documentation work should not begin until Phase 15 is explicitly requested and the remediated GitHub Actions runs are confirmed green.
