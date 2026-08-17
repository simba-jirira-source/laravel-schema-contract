# Database Support and Limitations

This document describes how **Laravel Schema Contract v0.1.0** handles database driver metadata for SQLite, MySQL/MariaDB, and PostgreSQL.

It lists verified behavior, known limitations, and how the package degrades when metadata is incomplete.

## Supported Drivers

| Driver | CI verification | Notes |
|---|---|---|
| SQLite | Default test suite | Fast default for local development and CI |
| MySQL / MariaDB | Optional `database-compatibility` workflow | Requires `SCHEMA_CONTRACT_DB_DRIVER=mysql` |
| PostgreSQL | Optional `database-compatibility` workflow | Requires `SCHEMA_CONTRACT_DB_DRIVER=pgsql` |

The package reads schema metadata through Laravel's `Schema::getColumns()` API and normalizes driver-specific shapes before contract analysis.

## Architecture

Driver-specific enrichment is isolated in:

- `SimbaJirira\SchemaContract\Support\Database\DriverColumnMetadataEnricher` — adjusts raw metadata per driver
- `SimbaJirira\SchemaContract\Support\ColumnTypeParser` — parses precision, scale, and length from driver type strings
- `SimbaJirira\SchemaContract\Support\DatabaseColumnNormalizer` — maps normalized driver types to `DatabaseType`

Unknown or incomplete metadata maps to `DatabaseType::Unknown` and produces conservative warnings rather than hard failures.

## Verified Type Handling

### Booleans

| Driver | Typical schema metadata | Normalized type |
|---|---|---|
| SQLite | `tinyint(1)` | `boolean` |
| MySQL/MariaDB | `tinyint(1)` | `boolean` |
| PostgreSQL | `boolean` / `bool` | `boolean` |

### Integers

| Driver | Metadata examples | Normalized types |
|---|---|---|
| SQLite | `integer` for all integer sizes | `integer` only |
| MySQL/MariaDB | `tinyint`, `smallint`, `mediumint`, `int`, `bigint` | `small_integer`, `integer`, `big_integer` |
| PostgreSQL | `int2`, `int4`, `int8` | `small_integer`, `integer`, `big_integer` |

### Decimal / Numeric

Precision and scale are preserved when the driver exposes them in the column type string, for example:

- MySQL/MariaDB: `decimal(10,2)`
- PostgreSQL: `numeric(10,2)`
- SQLite: often `numeric` without precision unless declared explicitly in SQL

When scale metadata is unavailable, decimal scale mismatch rules are skipped conservatively.

### JSON / JSONB

| Driver | Metadata | Normalized type |
|---|---|---|
| SQLite | `json` (with native JSON enabled) | `json` |
| MySQL/MariaDB | `json` | `json` |
| PostgreSQL | `json`, `jsonb` | `json` |

JSON columns without an explicit cast produce a warning, not an error.

### UUID

| Driver | Metadata | Normalized type |
|---|---|---|
| SQLite | `varchar` / `char(36)` | `string` |
| MySQL/MariaDB | `char(36)` / `varchar(36)` | `string` |
| PostgreSQL | `uuid` | `uuid` |

SQLite and MySQL/MariaDB do not expose a native UUID column type through Laravel's schema metadata in typical setups.

### Timestamps / Datetime

Supported normalized types:

- `date`
- `datetime`
- `timestamp` (including PostgreSQL `timestamptz`)

On SQLite, Laravel schema metadata typically reports both `timestamp()` and `dateTime()` columns as `datetime`.

Standard Laravel timestamp columns (`created_at`, `updated_at`) are excluded from date/time compatibility warnings.

### Enum

| Driver | Metadata | Normalized type |
|---|---|---|
| MySQL/MariaDB | `enum('a','b')` | `enum` |
| PostgreSQL | native enum/user-defined types may appear as custom names | often `unknown` unless recognized |
| SQLite | not native | usually `string` |

PostgreSQL native enum types are not fully verified across all deployment styles in v0.1.0.

### Text / String

`varchar`, `char`, `text`, PostgreSQL `character varying`, and related aliases normalize to `string` or `text`.

## Known Limitations

### SQLite

- Integer column sizes (`smallInteger`, `integer`, `bigInteger`) all report as `integer`.
- Blueprint-created `decimal()` columns may appear as `numeric` without precision until exposed by raw SQL.
- UUID columns are stored and reported as string-like types.
- Unsupported affinity/type names map to `unknown`.

### MySQL / MariaDB

- Requires a live database connection for integration verification.
- Some generated/virtual column metadata is ignored for contract analysis.
- `SET` columns and spatial types are currently mapped to `unknown`.

### PostgreSQL

- Requires a live database connection for integration verification.
- Custom user-defined types (including some enum deployments) may map to `unknown`.
- Extension types such as `geometry` / `geography` are not verified in v0.1.0.

### General

- The analyzer is read-only and never mutates schema or models.
- Missing precision/scale never produces false-positive decimal scale errors.
- Unsupported database types produce warnings through the compatibility matrix, not crashes.

## Running Driver Integration Tests Locally

SQLite tests run in the default suite.

For MySQL:

```bash
SCHEMA_CONTRACT_DB_DRIVER=mysql \
SCHEMA_CONTRACT_MYSQL_HOST=127.0.0.1 \
SCHEMA_CONTRACT_MYSQL_DATABASE=schema_contract \
SCHEMA_CONTRACT_MYSQL_USERNAME=root \
SCHEMA_CONTRACT_MYSQL_PASSWORD= \
vendor/bin/pest --group=mysql
```

For PostgreSQL:

```bash
SCHEMA_CONTRACT_DB_DRIVER=pgsql \
SCHEMA_CONTRACT_PGSQL_HOST=127.0.0.1 \
SCHEMA_CONTRACT_PGSQL_DATABASE=schema_contract \
SCHEMA_CONTRACT_PGSQL_USERNAME=postgres \
SCHEMA_CONTRACT_PGSQL_PASSWORD=postgres \
vendor/bin/pest --group=pgsql
```

If the driver is not configured, grouped tests are skipped rather than failed.
