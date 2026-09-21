---
name: developing-filament-packages
description: Use when creating, changing, documenting, testing, auditing, maintaining, or releasing a MortalKiller Filament package, including public API work, GitHub issues, roadmaps, CI, compatibility, security-sensitive behavior, documentation sites, or PlumbPHP release readiness.
---

# Developing Filament Packages

## Core principle

Treat a public package as a maintained product, not just code. The canonical complete standard lives in `mortalkiller/filament-package-template` at `docs/package-standard.md`; use the bundled `references/standard-summary.md` for the runtime summary.

## Start here

1. Identify the repository and version line. `main` is latest stable; `*.x` branches are major development/support lines.
2. Read the relevant issue, source, tests, Composer constraints, README, docs, and roadmap before changing behavior.
3. For meaningful work, use an issue with objective acceptance criteria and a focused temporary branch.

## API changes

Use Laravel/Filament-native behavior first. Keep the public API small, fluent, predictable, and backwards compatible inside a major.

**REQUIRED REFERENCE:** Read `references/api-review.md` before adding or changing public API.

Tests and documentation are part of the feature. Do not call implementation complete when only runtime code changed.

## Public documentation

Derive API docs from actual source and tests, not README assumptions.

Use synthetic examples. Never publish customer/private application names, real infrastructure, credentials, tokens, internal hosts, SSH details, container names, or private filesystem paths.

Keep the roadmap future-only: remove completed features instead of marking them as historical checklist items.

## Verification

Run the repository's CI-equivalent checks and verify every compatibility boundary declared in Composer. Add browser/JavaScript checks only when the package owns browser/JavaScript behavior.

Use the central package-standard checker when available.

## Releases

Release notes come from the previous tag → release state, not memory.

**REQUIRED REFERENCE:** Read `references/release-checklist.md` before preparing or publishing a release.

Do not call an actively maintained public package release-ready until PlumbPHP reports:

- Ecosystem 100
- Maintenance 100
- Security 100
- Composite 100

Investigate legitimate scanner findings, but never weaken security, compatibility, tests, or architecture merely to satisfy a scanner.

## Quick reference

| Situation | Required action |
|---|---|
| New package | Start from the canonical template and Standard v1 |
| Public API | Run the API review |
| Meaningful feature/bug | Issue + acceptance criteria + tests + docs |
| Public docs | Source audit + privacy audit |
| Roadmap update | Keep future work only |
| Release | Actual diff/history + release checklist + PlumbPHP 100 |

For branch, repository, CI, docs, privacy, and maintenance conventions, read `references/standard-summary.md`.
