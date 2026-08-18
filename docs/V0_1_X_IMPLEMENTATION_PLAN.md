# Laravel Schema Contract — v0.1.x Stabilization Implementation Plan

## Operating rule

Implement exactly one release at a time.

Before every release prompt, read:

- `docs/MASTER_SPEC.md`
- `docs/IMPLEMENTATION_PLAN.md`
- `docs/IMPLEMENTATION_STATUS.md`
- `docs/ARCHITECTURE_REVIEW.md`
- `docs/V0_1_X_STABILIZATION_SPEC.md`
- `docs/V0_1_X_ACCEPTANCE_CRITERIA.md`
- current source
- relevant tests
- relevant CI workflows

Do not start the next release until the maintainer explicitly asks.

## v0.1.2 — Release & Documentation Consistency

- reconcile implementation status with released/tagged state
- normalize v0.1.x documentation wording
- fix Laravel 13.x internal wording
- validate README/archive links
- full quality gate
- prepare v0.1.2 changelog

## v0.1.3 — In-Run Schema Metadata Caching

- add in-memory cache keyed by connection + table
- preserve inspector contract
- add cache behaviour tests
- verify no output/semantic regression
- prepare v0.1.3 changelog

## v0.1.4 — Model Discovery Hardening

- token-aware class/namespace parsing
- preserve model_paths/ignore semantics
- harden edge cases
- add discovery regression tests
- prepare v0.1.4 changelog

## v0.1.5 — MariaDB Verification

- add MariaDB service/job to database compatibility CI
- reuse existing MySQL group where sensible or add dedicated grouping when clearer
- verify representative schema metadata
- update database support docs
- prepare v0.1.5 changelog

## v0.1.6 — Database Metadata Hardening

- expand driver metadata tests/normalization
- improve PostgreSQL enum/UDT/jsonb/timestamptz coverage
- harden MySQL/MariaDB enum/SET/spatial degradation
- harden SQLite unusual affinity handling
- prepare v0.1.6 changelog

## v0.1.7 — Container Architecture

- bind interfaces/default implementations
- container-resolve analyzer graph
- inject analyzer into command
- avoid singleton mutable RuleRegistry
- preserve external APIs
- prepare v0.1.7 changelog

## v0.1.8 — CI & Distribution Reliability

- add lockfile baseline job using `composer install`
- preserve update matrices
- add Composer archive smoke verification
- add clean consumer-install smoke test
- prepare v0.1.8 changelog

## v0.1.9 — Pre-v0.2 Foundation Freeze

- add model inspection cache if justified
- add public/BC contract tests
- audit API surface and docs
- perform final v0.1 architecture review
- mark v0.1 line stable/frozen
- prepare v0.1.9 changelog
- do not implement v0.2

## Global completion rule

Every release must end with:
- files changed
- tests added/changed
- commands run
- exact results
- backwards-compatibility assessment
- known limitations
- explicit confirmation no v0.2 work was implemented
- explicit confirmation no Git/release action was performed
