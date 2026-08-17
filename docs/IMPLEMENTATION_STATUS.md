# Laravel Schema Contract — Implementation Status

> Updated by Phase 13 — Static Analysis and Architecture Quality (2026-08-17).

## Current Phase

**Phase 13 — Complete**

Next recommended phase: **Phase 14 — CI** (await explicit maintainer instruction).

## Current State

Static analysis is tightened to PHPStan level 8 with Larastan (via `phpstan/extension-installer`), architecture boundaries are enforced in Pest arch tests, and production dependencies explicitly declare the Artisan command package requirement.

### Phase 13 deliverables

- PHPStan raised to **level 8** with Larastan package tuning (`disableMigrationScan`, `disableSchemaScan`, `treatPhpDocTypesAsCertain: false`)
- Larastan loaded through `phpstan/extension-installer` (no duplicate neon include)
- Fixed nullable model connection resolution in `EloquentModelInspector`
- Added explicit production dependency on `illuminate/console`
- Added `composer check:composer` script (`composer validate --strict`) to the quality pipeline
- Expanded Pest architecture tests for layer boundaries, readonly DTOs, and reflection isolation
- Reviewed public APIs, DTO design, mixed usage boundaries, and service provider bindings (no premature container bindings added)

### Architecture review decisions

| Area | Decision |
|---|---|
| Service container bindings | Deferred — command constructs analyzer explicitly; no facade/manager layer for v0.1 |
| `mixed` usage | Retained only at Laravel/config/cast input boundaries (`CastNormalizer`, config readers) |
| Reflection | Confined to `EloquentModelDiscoverer` (arch test enforced) |
| DTOs | All readonly under `SimbaJirira\SchemaContract\DTO` |
| Production deps | `illuminate/console`, `illuminate/database`, `illuminate/support` only |

### Quality command results (Phase 13, 2026-08-17)

| Command | Result | Notes |
|---|---|---|
| `composer validate --strict` | Pass | |
| `composer analyse` (PHPStan L8 + Larastan) | Pass | 44 files; `--memory-limit=512M` |
| `composer lint:check` (Pint) | Pass | |
| `composer test:types` | Pass | 100% type coverage |
| `composer test:unit` | Pass | 279 passed, 6 skipped, 734 assertions |

## Existing Architecture

### Layer boundaries (enforced)

```text
Contracts ──► DTOs / Enums
Analysis / Rules / Compatibility ──► Inspectors / Support (no Console)
Console ──► Analysis + Discovery + Rendering
Discovery ──► Reflection (only layer allowed)
```

### Static analysis stack

```text
phpstan.neon.dist (level 8)
        ↓
phpstan/extension-installer → Larastan
        ↓
src/ + config/
```

## Dependencies

Production:

- `illuminate/console` (Artisan command)
- `illuminate/database` (schema + Eloquent)
- `illuminate/support` (service provider, config)

Development: Larastan, Pest, Testbench, Pint (unchanged scope).

## Testing State

| Layer | Status |
|---|---|
| Architecture — layer boundaries | `tests/ArchTest.php` (7 boundary rules + presets) |
| All prior phase tests | Unchanged and passing |

## CI State

Unchanged from Phase 12 (`tests.yml`, `database-compatibility.yml`). Phase 14 will align CI with the updated L8 analysis pipeline.

## Risks

1. **Parallel Testbench flakiness** — occasional config bootstrap race on Windows under `--parallel`.
2. **No container bindings yet** — programmatic extension still requires direct instantiation until a later phase justifies DI wiring.

## Conflicts With Master Specification

None identified for Phase 13 scope.

## Recommended Changes

### Phase 14 (when requested)

Wire `composer check:composer`, PHPStan L8, Pint, and Pest into the primary GitHub Actions workflow matrix.

### Later phases

Phases 15–17 per implementation plan.

## Phase Readiness

| Phase | Status |
|---|---|
| 0–12 | Complete |
| 13 — Static analysis / architecture | **Complete** |
| 14 — CI | **Ready to begin** |
| 15–17 | Blocked — await maintainer instruction |

---

## Decision: **READY** (for Phase 14)

Static analysis, architecture boundaries, and dependency declarations are aligned with v0.1 quality goals. CI hardening should not begin until Phase 14 is explicitly requested.
