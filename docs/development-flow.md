# Package development and release flow

## Branches

Use one permanent branch per package major: `1.x`, `2.x`, and so on. There is no separate stable-promotion branch. The default branch is the latest stable package major, or `1.x` before the first stable release. Create the next package major only when incompatible consumer-facing work starts. A Standard/tooling major does not by itself require a new package major.

Start each change from the affected major, create a focused `feature/*`, `fix/*`, `docs/*`, `test/*`, `refactor/*`, or `chore/*` branch, and open a PR back to that same major. Require review and passing CI before merging. Squash temporary work branches and delete them after merge. Keep supported major branches; freeze unsupported lines rather than importing incompatible code into them.

A branch head may contain unreleased work. A tag identifies exactly what was released. Do not create branches for every minor or patch release.

## Version numbers

Use `vMAJOR.MINOR.PATCH` tags: compatible bug fixes increment PATCH; compatible functionality increments MINOR; incompatible public API, runtime behavior or supported-platform changes require a MAJOR review. Package majors are independent from Filament majors, template majors and Standard majors. Never move or overwrite a published tag.

Adopting Standard v2 tooling on an existing package without changing its public API does not create a new package major.

Fix the oldest supported affected major first and forward-port the relevant change, with tests, to newer affected lines. Do not merge an old major wholesale into a different major. Release each affected line independently.

## Preparing a release

1. Choose the target package major and exact release commit, and inspect its diff from the previous tag on that major.
2. Complete code, tests, README, documentation, compatibility notes, upgrade instructions and release notes. Remove completed roadmap entries.
3. Wait for successful push CI on that exact commit: tests, code quality and package standard. Include PHP, minimum/latest dependency boundaries, Larastan and Zizmor where applicable, optional JavaScript/browser tests, and a documentation build.
4. Audit public content and verify PlumbPHP findings. Do not report 100 without a fresh scan; distinguish the scanned ref from the release commit when the service is stale.
5. Create the immutable `vX.Y.Z` tag at the verified commit on `X.x`, then publish its GitHub Release.
6. Check release documentation/publication and record the release on the relevant issues.

## Documentation

PRs and pushes build and validate documentation but never deploy stable docs. Stable GitHub Releases publish documentation from the exact tag after major-ancestry and exact-commit CI verification.

The canonical `https://docs.pedromonteiro.dev/<package>/` URL remains **Latest**. `/<package>/N.x/` contains the latest published documentation for that package major. Older majors, prereleases and stale reruns must not replace newer stable documentation.

## Repository setup and migration

For a new package, set the default branch to `1.x`; do not create an extra promotion branch. Configure protections on supported package-major branches, immutable release tags where available, required checks, and appropriate Dependabot targets.

For Standard v2 migration guidance, use the canonical guide:
https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/migrating-to-v2.md

## Shared workflow versions

External `uses:` references must use a full 40-character commit SHA. Pin checker/publication tooling to the same validated template commit using `standard-ref`. Moving template `2.x` does not update packages automatically; adopt a reviewed template commit in a separate PR and run CI.
