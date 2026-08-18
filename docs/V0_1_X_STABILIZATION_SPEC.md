# Laravel Schema Contract — v0.1.x Stabilization Specification

## 1. Purpose

The v0.1.x stabilization line exists to make the existing **Database Schema ↔ Eloquent Model** product line robust before Database ↔ Validation work starts in v0.2.

This train must not change the product milestone.

Existing product scope remains:

```text
Database Schema ↔ Eloquent Model
```

## 2. Backwards compatibility

Across v0.1.2 through v0.1.9, preserve unless a genuine bug requires otherwise:

- `php artisan schema-contract:check`
- optional targeted `{model}` argument
- exit codes `0`, `1`, `2`
- `model_paths`
- `ignore_models`
- `ignore_columns`
- `ContractAnalyzer`
- current DTO constructor semantics
- current rule identifiers
- service-provider auto-discovery
- current Composer package name and namespace
- SQLite/MySQL/PostgreSQL support

Any backwards-incompatible change belongs in a later minor release unless it is required to fix clearly broken behaviour and is explicitly approved by the maintainer.

## 3. Release train

### v0.1.2 — Release & documentation consistency

Goals:
- reconcile release/status docs with actual v0.1.1 state
- replace stale `v0.1.0` wording with `v0.1.x` where it describes the maintenance line
- make internal Laravel support wording match Composer's Laravel 13.x constraint
- validate archive/document links
- no runtime behaviour changes unless an actual defect is discovered

### v0.1.3 — In-run schema metadata caching

Goals:
- cache table schema metadata within one analysis run
- key by effective connection + table
- avoid duplicate `hasTable/getColumns` work
- no persistent cache
- no behaviour changes in findings/output

### v0.1.4 — Model discovery hardening

Goals:
- remove fragile regex-only class parsing
- use token-aware PHP source inspection
- preserve existing `model_paths` semantics
- improve correctness for comments, bracketed namespaces, unusual formatting, multiple declarations
- no Composer-wide discovery expansion

### v0.1.5 — MariaDB verification

Goals:
- add an actual MariaDB integration job
- verify representative current v0.1 data types
- keep MySQL verification
- make documentation claims match verified CI coverage

### v0.1.6 — Database metadata hardening

Goals:
- harden PostgreSQL/MySQL/MariaDB/SQLite metadata normalization
- improve coverage for enum/UDT/jsonb/timestamptz/unknown driver types
- preserve conservative `UNKNOWN` degradation
- do not add new database drivers

### v0.1.7 — Container architecture

Goals:
- bind core analyzer graph through Laravel's container
- remove manual analyzer construction from the command
- preserve existing public API
- do not expose the v0.9 public extension API early
- avoid mutable global singletons

### v0.1.8 — CI and distribution reliability

Goals:
- retain prefer-lowest/prefer-stable update matrices
- add deterministic lockfile-based validation
- add Composer archive/consumer-install smoke verification
- improve diagnosability of transient dependency failures
- do not weaken current CI

### v0.1.9 — Pre-v0.2 foundation freeze

Goals:
- add in-run model definition caching where justified
- add explicit backwards-compatibility contract tests
- audit public/internal API surface
- verify rule identifiers/exit codes/config compatibility
- perform final v0.1 architecture freeze and v0.2 readiness review

## 4. False-positive policy

False positives remain a product failure.

Only emit blocking errors for high-confidence incompatibilities.

Unknown/incomplete metadata must degrade safely.

No new aggressive rule behaviour should be added in the v0.1.x train.

## 5. Performance policy

Allowed:
- in-memory cache for schema metadata
- in-memory cache for model inspection
- avoiding duplicate reflection and schema calls

Not allowed:
- Redis cache
- filesystem cache
- application cache store
- persistent analysis cache
- cross-process stale state

## 6. Discovery policy

v0.1.4 may improve how existing model paths are parsed.

It must not:
- discover arbitrary Composer classes globally
- infer models from routes/controllers
- add validation source discovery
- change ignored-model semantics unnecessarily

## 7. CI policy

Keep:
- PHP 8.3 / 8.4 / 8.5
- prefer-lowest
- prefer-stable
- Windows
- SQLite
- MySQL
- PostgreSQL
- Pest
- PHPStan/Larastan
- Pint
- type coverage

Add MariaDB verification in v0.1.5.

Add deterministic lockfile/archive/consumer install verification in v0.1.8.

## 8. Documentation policy

Use `v0.1.x` when describing behaviour common to the maintenance line.

Use exact versions only for:
- changelog entries
- release notes
- release-specific historical details

Do not claim v0.2 functionality before it exists.

## 9. Release control

Cursor may prepare release notes/changelog when a version prompt explicitly instructs it.

Cursor must never:
- commit
- push
- merge
- tag
- create GitHub Releases
- publish Packagist releases

The maintainer retains release control.
