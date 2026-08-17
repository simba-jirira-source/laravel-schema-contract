# Security Policy

**PLEASE DON'T DISCLOSE SECURITY-RELATED ISSUES PUBLICLY, [SEE BELOW](#reporting-a-vulnerability).**

## Supported Versions

| Version | Supported |
|---|---|
| 0.1.x | Yes |
| < 0.1 | No |

Security fixes are provided for supported release lines. Upgrade to the latest patch release when available.

## Reporting a Vulnerability

If you discover a security vulnerability in Laravel Schema Contract, email **simba-jirira-source at github@simbajirira.com** rather than opening a public GitHub issue.

Include:

- affected package version
- Laravel and PHP versions
- clear steps to reproduce
- impact assessment if known

We aim to acknowledge reports promptly and coordinate responsible disclosure.

## Responsible disclosure

- Do not post exploit details, credentials, or production data in public issues or pull requests.
- Do not include database passwords, API keys, or customer records in reports.
- Schema excerpts (table/column definitions) and cast definitions are usually sufficient for reproduction.

## Analyzer behavior and privacy

The package is **read-only** analysis tooling. It does not mutate schema, models, or application data.

Command output and reports may include structural metadata such as model names, table names, column names, and type information. The analyzer does not intentionally dump application row data. Avoid attaching full database dumps or `.env` files to reports.

## Scope

This policy covers the `simba-jirira-source/laravel-schema-contract` package. Vulnerabilities in Laravel, database servers, or consuming application code should be reported to the appropriate upstream projects.
