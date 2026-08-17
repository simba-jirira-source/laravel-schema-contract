# Laravel Schema Contract — Master Package Specification

## 1. Package Identity

- **Package:** Laravel Schema Contract
- **Composer package:** `simba-jirira/laravel-schema-contract`
- **Repository:** `laravel-schema-contract`
- **Namespace:** `SimbaJirira\SchemaContract`
- **Primary command:** `php artisan schema-contract:check`
- **License:** MIT
- **Initial target:** `v0.1.0`

Laravel Schema Contract is developer tooling for Laravel applications. Its purpose is to detect inconsistencies between the contracts that describe application data.

Long-term contract chain:

```text
Database Schema
      ↓
Eloquent Model
      ↓
Validation
      ↓
PHP Enums
      ↓
API Resources
      ↓
Livewire State
```

The initial release is deliberately narrower:

```text
Database Schema ↔ Eloquent Model
```

## 2. Supported Stack

Minimum target:

- Laravel 13+
- PHP 8.3+
- Composer 2+
- Pest
- Laravel Pint
- Laravel package testbench appropriate for Laravel 13

Static analysis should be supported with PHPStan/Larastan when introduced by the implementation plan.

Avoid unnecessary production dependencies.

## 3. Product Promise

The package should eventually allow a developer to run:

```bash
php artisan schema-contract:check
```

and answer:

> Are the different layers of my Laravel application describing the same data contract?

For `v0.1.0`, that means checking whether database schema metadata and Eloquent model casts agree.

## 4. Core Principles

The package must be:

- Laravel-aware
- deterministic
- non-destructive
- CI-friendly
- database-driver aware
- extensible
- testable
- conservative about false positives
- safe to run against development/test databases
- useful without changing application source code

The analyzer must never mutate schema or models.

## 5. v0.1.0 Scope

Implement only:

1. Eloquent model discovery.
2. Effective connection/table resolution.
3. Database column inspection.
4. Eloquent cast inspection.
5. Database type normalization.
6. Cast type normalization.
7. Compatibility analysis.
8. Contract rule execution.
9. Structured analysis results.
10. Human-readable Artisan output.
11. CI-friendly exit codes.
12. Basic configuration/ignore controls.
13. Tests, CI, documentation, and release-readiness checks.

Do **not** implement in `v0.1.0`:

- FormRequest validation analysis
- API Resource analysis
- Livewire analysis
- baseline generation
- GitHub annotation output
- automatic fixes
- automatic releases

## 6. Example

Database:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->nullable();
    $table->boolean('active')->default(true);
    $table->decimal('credit_limit', 10, 2)->nullable();
    $table->json('preferences')->nullable();
    $table->timestamps();
});
```

Model:

```php
class User extends Model
{
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'credit_limit' => 'integer',
        ];
    }
}
```

Expected output concept:

```text
App\Models\User
Table: users

PASS    active
        database: boolean
        cast: boolean

ERROR   credit_limit
        database: decimal(10,2)
        cast: integer
        suggested: decimal:2

WARNING preferences
        database: json
        cast: none
        suggested: array
```

## 7. Severity Model

Use a typed severity enum.

- `ERROR`: high-confidence incompatible contract.
- `WARNING`: potentially unsafe or recommended improvement.
- `INFO`: useful non-blocking information.

Warnings should not fail CI by default.

## 8. Database Type Model

Normalize raw driver types into internal types such as:

- BOOLEAN
- INTEGER
- BIG_INTEGER
- SMALL_INTEGER
- DECIMAL
- FLOAT
- DOUBLE
- STRING
- TEXT
- DATE
- DATETIME
- TIMESTAMP
- JSON
- UUID
- ENUM
- BINARY
- UNKNOWN

Preserve metadata where available:

- nullable
- default
- length
- precision
- scale
- original driver type

Unknown types must degrade to `UNKNOWN`, not crash analysis.

## 9. Laravel Cast Model

Normalize model casts into internal types such as:

- BOOLEAN
- INTEGER
- FLOAT
- DOUBLE
- DECIMAL
- STRING
- ARRAY
- OBJECT
- COLLECTION
- DATE
- DATETIME
- TIMESTAMP
- ENUM
- CUSTOM
- UNKNOWN

Retain original cast metadata.

Example:

```text
decimal:2
```

should retain scale `2`.

Custom cast classes must be recognized without being misclassified.

## 10. Initial Compatibility Expectations

| Database type | Compatible Eloquent representation |
|---|---|
| boolean | boolean |
| integer | integer |
| bigint | integer |
| smallint | integer |
| decimal | decimal |
| float | float/double/real |
| double | double/float/real |
| date | date-compatible cast |
| datetime | datetime-compatible cast |
| timestamp | datetime/timestamp-compatible cast |
| json/jsonb | array/object/collection-compatible cast |
| uuid | string or no cast |
| string/varchar/char | string or no cast |
| text | string or no cast |
| enum | string, PHP enum, or supported enum cast |

Compatibility must be centralized rather than scattered across rules.

## 11. Decimal Rules

Given:

```php
$table->decimal('price', 10, 2);
```

this is an error:

```php
'price' => 'integer',
```

this should pass:

```php
'price' => 'decimal:2',
```

Where metadata is available, scale mismatch should be detectable:

```text
database: decimal(12,4)
model:    decimal:2
```

## 12. JSON Rules

JSON columns should accept compatible representations such as array/object/collection casts.

A JSON column with no explicit model cast should normally be a warning rather than an error in the initial release.

Built-in Laravel custom cast classes should be supported incrementally.

## 13. Date/Time Rules

Recognize:

- `date`
- `datetime`
- `immutable_date`
- `immutable_datetime`
- `timestamp`

Do not emit noisy false positives for normal Laravel timestamps such as `created_at` and `updated_at`.

## 14. Model Discovery

Default discovery path:

```php
app_path('Models')
```

Configurable through:

```php
'model_paths' => [
    app_path('Models'),
],
```

Discovery must:

- find concrete Eloquent models
- support nested namespaces
- skip abstract classes
- skip non-model classes
- avoid duplicates
- support ignored models
- remain separate from analysis

## 15. Model/Table Resolution

For each model determine:

- model class
- effective connection
- effective table
- primary key
- casts

Respect custom `$table` and `$connection`.

Never assume all models use the default connection.

## 16. Schema Inspection

Use a dedicated abstraction.

Concept:

```php
interface SchemaInspector
{
    public function inspect(ModelDefinition $model): TableDefinition;
}
```

First-class target drivers:

- SQLite
- MySQL/MariaDB
- PostgreSQL

Driver capabilities differ. Missing precision/scale or other metadata must degrade gracefully.

## 17. Model Inspection

Use a dedicated model inspector.

Concept:

```php
interface ModelInspector
{
    public function inspect(string $modelClass): ModelDefinition;
}
```

Do not spread reflection logic throughout the package.

## 18. DTOs

Prefer immutable or effectively immutable DTOs.

Expected concepts:

- ModelDefinition
- TableDefinition
- ColumnDefinition
- CastDefinition
- ContractViolation
- ContractResult
- AnalysisSummary

Use enums and typed fields instead of unbounded arrays for core state.

## 19. Rule Architecture

Contract checks should be discrete rules.

Concept:

```php
interface ContractRule
{
    public function analyze(
        ModelDefinition $model,
        ColumnDefinition $column,
    ): array;
}
```

Initial rule concepts:

- CastMatchesColumnType
- DecimalScaleMatches
- JsonColumnHasCompatibleCast
- DateColumnHasCompatibleCast

Rules return structured violations. They must not render CLI output.

## 20. Rule Registry

Provide a central rule registry responsible for built-in rules and future extension.

Do not over-engineer third-party extension in v0.1.0, but avoid preventing it.

Future concept:

```php
SchemaContract::extend(
    new CompanySpecificContractRule()
);
```

## 21. Analyzer

The analyzer orchestrates:

1. model inspection
2. schema inspection
3. rule execution
4. violation collection
5. result generation
6. summaries

It must be presentation-independent.

Programmatic analysis should be possible.

## 22. Artisan Command

Primary command:

```bash
php artisan schema-contract:check
```

Specific model:

```bash
php artisan schema-contract:check User
php artisan schema-contract:check "App\Models\User"
```

Default summary concept:

```text
Models inspected: 14
Columns inspected: 127
Errors: 2
Warnings: 5
Passed: 120
```

## 23. Exit Codes

Default:

- `0` = no blocking contract errors
- `1` = contract errors detected
- `2` = configuration/runtime/command failure

Warnings do not fail by default.

## 24. Future CLI Design

Architecture should permit later options such as:

```bash
php artisan schema-contract:check --strict
php artisan schema-contract:check --json
php artisan schema-contract:check --format=github
```

Do not implement future output formats early.

## 25. Configuration

Publishable config:

```text
config/schema-contract.php
```

Initial options should remain small:

```php
return [
    'model_paths' => [
        app_path('Models'),
    ],

    'ignore_models' => [
        //
    ],

    'ignore_columns' => [
        //
    ],
];
```

Avoid speculative config.

## 26. Service Provider

Create/maintain:

```text
SchemaContractServiceProvider
```

Responsibilities:

- register config
- register bindings
- register commands
- publish config
- support Laravel auto-discovery

A facade is optional and should not be added without a concrete benefit.

## 27. Testing

Use Pest.

Test layers:

- Unit
- Feature
- Integration
- Architecture where useful

Unit coverage should include:

- database normalization
- cast normalization
- type compatibility
- severity
- rules
- decimal scale
- JSON compatibility
- date compatibility

Feature coverage should include:

- command success
- command failures
- warnings
- model targeting
- exit codes
- missing table
- custom table

Integration tests should use realistic schemas.

SQLite can be the fast default. Additional drivers should be tested where CI allows.

## 28. Static Analysis and Style

Use Laravel Pint.

Design for PHPStan/Larastan:

- avoid widespread `mixed`
- avoid unbounded arrays for core state
- use enums/DTOs/interfaces where justified
- keep reflection localized
- type public APIs

Use modern PHP conventions consistently.

## 29. Error Handling

Handle gracefully:

- unavailable database
- missing table
- invalid model path
- unsupported metadata
- abstract model
- custom cast
- unsupported database type

Example:

```text
WARNING App\Models\LegacyRecord

Column "metadata" uses unsupported database type "geography".
Type comparison skipped.
```

## 30. False-Positive Policy

False positives are a product failure.

Only use `ERROR` where confidence is high.

Prefer `WARNING` or no result when metadata or intent is uncertain.

## 31. Performance

Within one analysis run:

- cache schema metadata
- avoid duplicate table inspection
- avoid unnecessary model booting
- avoid repeated reflection work where reasonable

No persistent cache is required for v0.1.0.

## 32. Security

Reports may include:

- model names
- table names
- column names
- types
- contract metadata

Do not dump application records.

Be careful with sensitive defaults.

## 33. README Requirements

Document only implemented behavior:

1. purpose
2. requirements
3. installation
4. quick start
5. example violation
6. commands
7. configuration
8. supported databases/types
9. CI usage
10. limitations
11. roadmap
12. contributing
13. security
14. license

Recommended development install:

```bash
composer require simba-jirira/laravel-schema-contract --dev
```

## 34. Branch Strategy

Recommended:

```text
development → main
```

Normal work occurs on `development`.

Release-ready work is merged to `main`.

Cursor must never create or push tags or create a GitHub release unless explicitly instructed.

## 35. Changelog

Use Keep a Changelog conventions.

Always retain:

```markdown
## [Unreleased]
```

above released versions.

Cursor may update the changelog when a phase explicitly calls for it, but may not release automatically.

## 36. Roadmap

- `v0.1` Database ↔ Eloquent
- `v0.2` Database ↔ Validation
- `v0.3` Enums, defaults, nullability
- `v0.4` Model ↔ FormRequest
- `v0.5` API Resource contracts
- `v0.6` Livewire 4 contracts
- `v0.7` CI/JSON/GitHub reporting
- `v0.8` Baselines/suppression
- `v0.9` Public extension API
- `v1.0` Stable public API

Each release must be independently useful.

## 37. Proposed Source Layout

Target architecture:

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

This is a target, not permission to create everything in Phase 1.

## 38. Definition of Done for v0.1.0

A release candidate should satisfy all of the following:

- installs into Laravel 13+
- package auto-discovery works
- model discovery works
- custom table names work
- custom connections are respected
- casts are inspected
- database columns are inspected
- types are normalized
- compatibility checks work
- decimal mismatches are detected
- boolean mismatches are detected
- JSON compatibility is handled
- date compatibility is handled
- unsupported types degrade gracefully
- errors/warnings are distinguished
- command output is readable
- CI exit codes work
- configuration/ignores work
- tests pass
- Pint passes
- static analysis passes when configured
- CI passes
- README is accurate
- CHANGELOG is ready
- no automatic release occurred

## 39. Cursor Development Rules

Before each phase:

1. read `docs/MASTER_SPEC.md`
2. read `docs/IMPLEMENTATION_PLAN.md`
3. inspect the current repository
4. inspect relevant tests
5. compare the current state to the requested phase
6. implement only that phase

Never silently implement future roadmap functionality.

Every meaningful behavior requires tests.

Do not delete or weaken tests merely to obtain a green build.

Do not create tags, releases, or publish to Packagist unless explicitly instructed.

At the end of every implementation phase report:

- files added
- files changed
- architecture decisions
- tests added
- commands run
- results
- known limitations
- next phase

## 40. Long-Term Goal

Laravel Schema Contract should grow into a framework-aware contract consistency engine that checks:

```text
Database ↔ Eloquent ↔ Validation ↔ Enums ↔ API Resources ↔ Livewire
```

while remaining deterministic, extensible, and conservative about false positives.
