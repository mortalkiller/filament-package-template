---
name: developing-filament-packages
description: Use when creating, changing, documenting, testing, auditing, maintaining, or releasing a MortalKiller Filament package, including public API work, GitHub issues, roadmaps, CI, compatibility, security-sensitive behavior, documentation sites, or PlumbPHP release readiness.
---

# Developing Filament Packages

## Core principle

Treat a public package as a maintained product. The canonical standard is `docs/package-standard.md` in `mortalkiller/filament-package-template`; read `references/standard-summary.md` for the runtime summary.

## Start here

1. Identify the repository and package major. Use the major-only flow: permanent `*.x` branches, temporary work branches, and immutable release tags. Do not recreate a separate `main` promotion branch.
2. Read the issue, relevant source/tests, Composer constraints, README, docs and roadmap before changing behavior.
3. Create a focused temporary branch from the affected major and target that major in the PR. Meaningful work needs an issue and objective acceptance criteria.

## API changes

Prefer native Laravel/Filament behavior and a small fluent, predictable API. Preserve backwards compatibility inside a major.

**REQUIRED REFERENCE:** Read `references/api-review.md` before adding or changing public API. Tests and source-derived documentation are part of the feature.

## Documentation and privacy

Derive API docs from source and tests, not old README assumptions. Use fictional examples; never publish private consumers, infrastructure, credentials, tokens, hosts, SSH details, real container names, paths or screenshots. Keep the roadmap future-only.

PRs and pushes validate docs only. Stable releases publish the exact tag into its major channel, and update Latest only when semantically newest. Prereleases, old majors and stale reruns cannot replace newer stable documentation. Manual publication defaults to a dry run of an existing release.

## Verification

Run CI-equivalent checks and test every declared compatibility boundary. Include browser/JavaScript tests only where relevant. Run the standard checker. External workflow calls and their tooling must share a validated full SHA; template branch updates are not adopted automatically.

## Releases

**REQUIRED REFERENCE:** Read `references/release-checklist.md` before preparing or publishing a release.

Inspect the previous tag on the same major → exact release-commit diff. After review and successful exact-commit CI, create a new `vX.Y.Z` tag on `X.x` and publish its GitHub Release. Do not move tags or merge between permanent branches just to release. Keep the default branch on the newest stable major; change it only when the next major is stable.

Do not claim release completion without the applicable checks, public docs verification and fresh PlumbPHP evidence:

- Ecosystem 100
- Maintenance 100
- Security 100
- Composite 100

Never weaken security, compatibility, tests or architecture to satisfy a scanner. Distinguish stale scans and unexecuted live publication from verified results. Update installed skill copies after changing the canonical skill.
