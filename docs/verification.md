# Verification record

This record describes the major-only migration checked on 2026-09-21. Code integration, CI, repository administration, release publication and live documentation deployment are separate milestones. Passing CI is not evidence that a live release deployment has occurred.

The authoritative development and release rules are in [package-standard.md](package-standard.md), [development-flow.md](development-flow.md) and [releasing.md](releasing.md). Use permanent `N.x` branches and immutable release tags; do not restore the former promotion-branch model.

## Preserved historical evidence

The full bootstrap, earlier cross-repository pilot, initializer and skill verification record is preserved at [commit 4422bae](https://github.com/mortalkiller/filament-package-template/blob/4422bae7a57fe10b613cc04560ab81b3b973024a/docs/verification.md).

That historical record describes the previous deployment model and historical PlumbPHP results. Its instructions to use `main` as the default or release-promotion branch are superseded. Its successful legacy deployments do not prove the new release-tag deployment path, and its scanner scores are not a fresh verification of the migrated commits.

## Shared implementation

The migration was integrated into `1.x` at `bca10ac79b2660fda013ef7e8ce2b5012e584e7a`. The consumer packages pin reusable workflows and supporting tooling to the validated commit `fd1b3f6ef3fd383de952c8a4f210c51b49f63e36`.

Freshly inspected successful pull-request runs for that tooling commit:

- [Package standard](https://github.com/mortalkiller/filament-package-template/actions/runs/35657320429).
- [Package tests](https://github.com/mortalkiller/filament-package-template/actions/runs/35657320478).
- [Documentation](https://github.com/mortalkiller/filament-package-template/actions/runs/35657320398).
- [Code quality](https://github.com/mortalkiller/filament-package-template/actions/runs/35657320435).

The implementation contains the release planner, semantic channel selection, exact-tag checkout, major ancestry and exact-commit CI guards, safe major-directory synchronization, documentation version selector, initializer changes, updated skill and regression checks. PR and major-push documentation callers set `deploy: false` and do not inherit deployment secrets.

## Consumer migration evidence

### Filament Page Header

Migration commit: `ac6517572306341a59d9d2de44fa40e45eb29010` on `2.x`.

The following push runs were inspected and passed:

- [Package tests](https://github.com/mortalkiller/filament-page-header/actions/runs/35658407985), including PHP compatibility jobs, JavaScript and both browser jobs.
- [Code quality](https://github.com/mortalkiller/filament-page-header/actions/runs/35658407789).
- [Package standard](https://github.com/mortalkiller/filament-page-header/actions/runs/35658407700).
- [Documentation](https://github.com/mortalkiller/filament-page-header/actions/runs/35658407603); the deploy job was skipped as intended.

[PR #32](https://github.com/mortalkiller/filament-page-header/pull/32) subsequently removed the README badge dependency on the former promotion branch, after all four PR workflows passed. This is a documentation-only follow-up; verify the resulting branch-tip push checks before tagging a release.

### Filament Complete User Profile

Migration commit: `d68bcc4c3a237e7702040486a85e7b157319ae48` on `1.x`.

The following push runs were inspected and passed:

- [Tests](https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35658428608).
- [Code quality](https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35658428706).
- [Package standard](https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35658428845).
- [Documentation](https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35658428589).

[PR #9](https://github.com/mortalkiller/filament-complete-user-profile/pull/9) subsequently corrected the README badge to `1.x`, after all four PR workflows passed. The existing Filament compatibility floor and test matrix were preserved. Verify the resulting branch-tip push checks before tagging a release.

## Preservation before retiring the old branch

[PR #14](https://github.com/mortalkiller/filament-package-template/pull/14) preserved the previously merged Dependabot updates and the old template branch history in `1.x`, using a normal merge at `4422bae7a57fe10b613cc04560ab81b3b973024a`.

The following PR checks passed before that merge:

- [Package standard](https://github.com/mortalkiller/filament-package-template/actions/runs/35660065500).
- [Package tests](https://github.com/mortalkiller/filament-package-template/actions/runs/35660065517).
- [Code quality](https://github.com/mortalkiller/filament-package-template/actions/runs/35660065469).
- [Documentation](https://github.com/mortalkiller/filament-package-template/actions/runs/35660065496).

GitHub compare results confirmed the inspected old branch tips are ancestors of the corresponding major lines. No old branch or published tag was deleted or moved by this migration. The maintainer will delete the old branches after the administrative checks below.

Consumer workflow pins were intentionally not changed by the preservation merge. A new template commit is adopted explicitly, with package CI verification, rather than through an implicit moving branch reference.

## Operational acceptance still required

At inspection, the template repository still used `main` as its default branch; the two consumer repositories already used their correct major branches. The connected tooling does not expose repository-administration writes or environment-policy management. These items must be verified separately:

- Set the template default branch to `1.x`; keep the header on `2.x` and the profile on `1.x`.
- Review protections and required checks for maintained major branches, including the template's `1.x`.
- Review `docs-production` branch/tag policies so eligible stable release tags are permitted. Allow maintained major branches only when deliberate manual re-publication is needed. Do not remove existing reviewer or security protections.
- Verify existing deployment configuration without exposing secret values. Real publication requires `DOCS_DEPLOY_ENABLED=true`.
- Enable the template repository setting when the GitHub template-generation feature is intended.
- Update installed local copies of the agent skill; repository changes do not update those copies automatically.
- Publish and verify the first genuine stable release containing the migration. A historical tag without the versioned documentation configuration must not be moved or silently built from newer branch code.
- Confirm a fresh PlumbPHP result for the relevant scanned ref before declaring package release acceptance complete.

No artificial public release was created for testing. No successful live deployment through the new release-tag path is asserted here. The existing public documentation remains separate from the newly integrated code until a verified release publication occurs.
