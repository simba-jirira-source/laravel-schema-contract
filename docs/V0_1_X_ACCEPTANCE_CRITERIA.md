# Laravel Schema Contract — v0.1.x Stabilization Acceptance Criteria

## Global acceptance criteria for every release

- Existing v0.1 behaviour remains backwards compatible.
- Existing command name remains unchanged.
- Existing exit codes remain `0`, `1`, `2`.
- Existing configuration keys remain valid.
- Existing package identity and namespace remain unchanged.
- No v0.2 validation functionality is introduced.
- Composer constraints are not expanded without explicit maintainer instruction.
- PHPStan/Larastan quality is not weakened.
- Pest coverage is not weakened.
- 100% Pest type coverage remains enforced.
- Pint remains enforced.
- CI matrix is not reduced.
- Database verification is not reduced.
- Changelog keeps an empty `[Unreleased]` section above the new version.
- Cursor performs no Git write/release operations.

## v0.1.2

- implementation status reflects actual v0.1.1 release state
- README uses v0.1.x where appropriate
- database support docs use v0.1.x where appropriate
- internal Laravel wording matches Laravel 13.x
- no stale "prepared/not released" state remains
- archive links remain valid

## v0.1.3

- repeated same connection/table inspection is cached
- different connection/table keys remain isolated
- missing-table behaviour is preserved
- no persistent cache exists
- existing analyzer output is unchanged

## v0.1.4

- class discovery is token-aware
- comments/strings do not create false classes
- bracketed namespaces are handled or explicitly/cleanly unsupported with tests
- nested namespaces and multiple files work
- abstract/non-model/trait/interface/enum filtering remains correct
- ordering remains deterministic
- ignore_models behaviour remains intact

## v0.1.5

- MariaDB runs in GitHub Actions
- representative MariaDB types are verified
- MySQL and PostgreSQL jobs remain
- README/database docs distinguish verified behaviour accurately

## v0.1.6

- PostgreSQL json/jsonb/uuid/date-time handling remains verified
- PostgreSQL enum/UDT behaviour is better tested/documented
- MySQL/MariaDB edge metadata degrades safely
- SQLite unusual types degrade safely
- no unsupported type is falsely classified with high confidence

## v0.1.7

- command no longer manually constructs the default analyzer graph
- default interfaces/implementations are container-resolvable
- RuleRegistry is not shared as a mutable singleton
- programmatic `ContractAnalyzer` usage remains compatible
- existing command behaviour/output remains compatible

## v0.1.8

- a deterministic lockfile-based job exists
- prefer-lowest/prefer-stable jobs remain
- Composer archive is inspected in CI or equivalent release smoke test
- package installs successfully from a built distribution in a minimal consumer test
- no generated archive is committed

## v0.1.9

- model inspection duplicate work is reduced where safely possible
- public/API compatibility tests exist
- rule identifiers are covered
- exit codes are covered
- config keys/default semantics are covered
- Composer archive is audited
- all DB/quality workflows pass
- final architecture review identifies v0.2 starting constraints
- no v0.2 source implementation exists
