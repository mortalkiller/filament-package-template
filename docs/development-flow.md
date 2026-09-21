# Package development and release flow

## Branches

Use one permanent branch per package major: `1.x`, `2.x`, and so on. There is no separate stable-promotion branch. The default branch is the latest stable major, or `1.x` before the first stable release. Create the next major only when work on incompatible changes starts. Do not change the default branch merely because an experimental next major exists.

Start each change from the affected major, create a focused `feature/*`, `fix/*`, `docs/*`, `test/*`, `refactor/*`, or `chore/*` branch, and open a PR back to that same major. Require review and passing CI before merging. Squash temporary work branches and delete them after merge. Keep supported major branches; freeze unsupported lines rather than importing incompatible code into them.

A branch head may contain unreleased work. A tag identifies exactly what was released. Do not create branches for every minor or patch release.

## Version numbers

Use `vMAJOR.MINOR.PATCH` tags: compatible bug fixes increment PATCH; compatible functionality increments MINOR; incompatible public API or support changes require a MAJOR review. Package majors are not Filament majors. Never move or overwrite a published tag.

Example: develop a compatible option on a temporary branch from `2.x`, merge it into `2.x`, and release `v2.4.0` from the verified commit. A later compatible fix becomes `v2.4.1`. Develop incompatible changes on `3.x` and release `v3.0.0` from that line when ready. Keep `2.x` as the default until the first stable v3 release is complete.

Fix the oldest supported affected major first and forward-port the relevant change, with tests, to newer affected lines. Do not merge an old major wholesale into a different major. Release each affected line independently.

## Preparing a release

1. Choose the target major and exact release commit, and inspect its diff from the previous tag on that major.
2. Complete code, tests, README, documentation, compatibility notes, upgrade instructions and release notes. Remove completed roadmap entries.
3. Wait for successful push CI on that exact commit: tests, code quality and package standard. Include PHP, minimum/latest dependency boundaries, optional static analysis and browser/JavaScript tests, and a documentation build.
4. Audit public content and verify PlumbPHP findings. Do not report 100 without a fresh scan; distinguish the scanned ref from the release commit when the service is stale.
5. Create the immutable `vX.Y.Z` tag at the verified commit on `X.x`, then publish its GitHub Release. There is no additional merge into another permanent branch.
6. Check the release-documentation workflow and the deployed version, and record the release on the relevant issues. A successful tag creation alone does not mean publication is complete.

A prerelease may use a tag such as `v3.0.0-rc.1`, explicitly marked as a prerelease. It never replaces stable documentation or changes the default major automatically.

## Documentation

PRs and pushes build and validate documentation but never deploy it. Publishing a stable GitHub Release builds the source from its exact tag, verifies that the commit belongs to the corresponding major and has successful required push workflows, and then publishes it through `docs-production`.

The canonical `https://docs.pedromonteiro.dev/<package>/` URL remains **Latest**. `/<package>/2.x/` presents the newest published documentation for major 2. Only the greatest stable semantic version may update Latest; an old-major patch cannot replace a newer major. The publisher also checks `versions.json` to prevent rollback during re-runs or stale API reads. It preserves other major directories when synchronizing Latest.

A version selector lists deployed channels, not branches or unreleased versions. Historical majors are not fabricated from the current documentation. A historical tag lacking the versioned-docs configuration must not be moved; introduce the configuration in a new release of a supported line.

The **Release documentation** workflow accepts an existing stable `release-tag` for a manual re-run. `dry-run` defaults to true: it validates and builds without SSH or deployment. Actual manual publication must run from a major branch, requires `DOCS_DEPLOY_ENABLED=true`, and observes the same rollback and CI checks as automatic publication. Re-run after CI completes if publication initially fails because checks were still running. GitHub may supersede a pending concurrency run; manually re-run an affected release if its major channel was not published.

## Repository setup and migration

For a new package, set the default branch to `1.x`; do not create an extra promotion branch. Configure protections on supported major branches, immutable release tags where available, required checks, and the appropriate Dependabot targets. Keep secrets in the GitHub environment/repository, never in source.

`docs-production` must permit stable release tags as well as the maintained major branches used for manual re-runs. `DOCS_REMOTE_PATH` must be the absolute package directory ending in the repository slug, not the shared documentation root. The publishing runner needs Node.js and rsync; the server needs SSH, a POSIX shell and rsync. No new application/server runtime is required.

When migrating an existing repository, preserve any exclusive commits before removing an old branch. Update default branches, PR targets, badges, edit links, explicit `dev-main` consumers, deployment rules and agent-skill configuration. Laravel Boost-managed third-party skills are re-discovered and synchronized with `php artisan boost:update`. Preserve all published tags. Do not delete the old branch until these checks are complete.

## Shared workflow versions

External `uses:` references must use a full 40-character commit SHA. Pin the checker/publication tooling checkout to the same validated template commit using `standard-ref`. Moving a template branch does not update packages automatically; adopt a reviewed template commit in a separate PR and run CI. Local reusable workflow calls inside the template use `./.github/workflows/...` and therefore share the caller commit.
