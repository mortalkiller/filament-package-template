# Verification record

This file records verification evidence for MortalKiller Filament Package Standard v1 infrastructure.

It intentionally contains no credentials, private application/customer information, production infrastructure values, or private repository data.

## Template bootstrap

The temporary bootstrap workflow verified the template directly before the canonical reusable workflows were enabled.

Successful run:

- Bootstrap verification: https://github.com/mortalkiller/filament-package-template/actions/runs/35632702755

That run covered:

- `composer validate --strict`;
- dependency installation;
- `composer check` (Pint, PHPStan and PHPUnit);
- package initializer smoke test;
- collaboration/repository file smoke test;
- package-standard checker positive/negative fixture suite;
- package-standard self-check using `--skip-plumb`;
- Astro/Starlight dependency installation and production build.

## Canonical pull-request workflows

Pull request #2 validates the actual reusable-workflow integration.

Verified against commit `a43adedb360f2ff328c745ee452e8448390bec2f`:

- Package tests: https://github.com/mortalkiller/filament-package-template/actions/runs/35633115334 — success.
- Code quality: https://github.com/mortalkiller/filament-package-template/actions/runs/35633115350 — success.
- Documentation: https://github.com/mortalkiller/filament-package-template/actions/runs/35633115286 — build success; production deploy correctly skipped for the pull request.
- Package standard: https://github.com/mortalkiller/filament-package-template/actions/runs/35633115445 — success.

## Package-standard checker

The checker currently validates objective repository requirements including:

- required public package files/workflow wrappers;
- Composer package identity/type/license metadata;
- canonical documentation URL and PlumbPHP badges for package-profile checks;
- Starlight site/base/repository/personal-site configuration;
- configured sensitive public-content patterns;
- Git distribution hygiene.

Fixture coverage includes:

- valid package;
- invalid Composer owner;
- mismatched documentation base path;
- synthetic private-key marker;
- safe examples such as `127.0.0.1`, `example.com`, `/var/www/app` and `DOCS_HOST`;
- development browser artifact leaking into `git archive`;
- JSON output.

The template repository uses `--skip-plumb` because it is infrastructure rather than a published Filament package. Maintained public packages do not skip the Plumb badge check.

## Cross-repository pilot verification

The reusable workflows are now verified from real consumer repositories against `mortalkiller/filament-package-template@1.x`.

### filament-complete-user-profile

Stable `main` push evidence:

- Package tests: https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35643432097 — success.
- Code quality: https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35643432079 — success.
- Package standard: https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35643432090 — success.
- Documentation: https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35643432121 — success.

The documentation run proves the cross-repository deployment design:

- reusable workflow resolved from the Standard v1 repository;
- the consumer repository's `docs-production` environment was applied;
- environment secrets resolved successfully;
- SSH configuration succeeded;
- production `rsync` deployment succeeded.

PlumbPHP verification run:

- https://github.com/mortalkiller/filament-complete-user-profile/actions/runs/35643716613
- Ecosystem 100;
- Maintenance 100;
- Security 100;
- Composite 100.

### filament-page-header

Active `2.x` Standard v1 verification:

- Package tests: https://github.com/mortalkiller/filament-page-header/actions/runs/35645305814 — success.
- Code quality: https://github.com/mortalkiller/filament-page-header/actions/runs/35645305495 — success.
- Package standard: https://github.com/mortalkiller/filament-page-header/actions/runs/35645305865 — success.
- Documentation: https://github.com/mortalkiller/filament-page-header/actions/runs/35645305375 — success.

The package-test run includes:

- Filament 4.12.6 exact minimum;
- Filament 5.8.1 exact minimum;
- latest supported Filament 4/5 combinations through PHP 8.5;
- JavaScript unit tests;
- Playwright Chromium on Filament 4.12.6;
- Playwright Chromium on latest resolved Filament `^5.8.1`.

Stable `main` verification after synchronizing published v2.3.1 and applying Standard v1 infrastructure:

- Package tests: https://github.com/mortalkiller/filament-page-header/actions/runs/35647818493 — success.
- Code quality: https://github.com/mortalkiller/filament-page-header/actions/runs/35647818496 — success.
- Package standard: https://github.com/mortalkiller/filament-page-header/actions/runs/35647818424 — success.
- Documentation: https://github.com/mortalkiller/filament-page-header/actions/runs/35647818418 — success, including production docs deployment.

Current PlumbPHP stable-release scan still references v2.3.1 and therefore evaluates the pre-Standard docs workflow. Its current scores are Ecosystem 100, Maintenance 100, Security 78.38 and Composite 88.11. The only non-passing security check is `security.actions-sha-pinned`, with evidence pointing to four `@v4` references in the v2.3.1 docs workflow. Current `main` and `2.x` use the Standard v1 reusable docs workflow with SHA-pinned actions. A new stable patch release is required for Plumb to evaluate that corrected stable state.

## Current compatibility and quality boundaries

Verified after the initializer recovery and checker-hardening work:

- Package tests: https://github.com/mortalkiller/filament-package-template/actions/runs/35634685752 — success.
  - PHP 8.3 / Testbench ^11.0 / Filament 5.8.1 exact minimum — success.
  - PHP 8.5 / Testbench ^11.0 / latest resolved Filament ^5.8.1 — success.
- Code quality: https://github.com/mortalkiller/filament-package-template/actions/runs/35634685761 — success.
  - Pint — success.
  - PHPStan over `src/` and `tools/` — success.
  - tracked PHP/JavaScript syntax — success.
- Documentation: https://github.com/mortalkiller/filament-package-template/actions/runs/35634685717 — build success; PR deploy correctly skipped.
- Package standard: https://github.com/mortalkiller/filament-package-template/actions/runs/35634685763 — success.

The package initializer now also verifies safe resume after an interrupted initialization when the same persisted arguments are used, rejects mismatched resume arguments, and still rejects a second run after successful initialization.

The public-content checker tests generic host-only filesystem paths, literal SSH infrastructure commands, text assets under `docs-site/public/`, configured secret markers, safe fictional/local examples, and Git worktree distribution behavior.

## Agent skill status

The `developing-filament-packages` skill is now authored directly from the approved Standard v1.

Verified runtime package:

```text
skills/developing-filament-packages/
├── SKILL.md
├── README.md
├── install.sh
└── references/
    ├── api-review.md
    ├── release-checklist.md
    └── standard-summary.md
```

Static verification covers:

- required Agent Skills frontmatter and trigger description;
- main skill size capped at 500 words;
- Standard v1 source-of-truth reference;
- branch conventions;
- PlumbPHP Ecosystem/Maintenance/Security/Composite 100 gates;
- required API/release/standard reference files.

Installer verification covers:

- normal install into an arbitrary skills directory;
- installation of `SKILL.md` plus all runtime references;
- refusal to overwrite an existing installation by default;
- deliberate replacement only through `--force`.

Verified in the package-test matrix on commit `c36ade5b5a32644db4f60e5f982126fdf63d9358`:

- PHP 8.3 / exact minimum Filament boundary — success;
- PHP 8.5 / latest allowed Filament boundary — success.

The maintainer explicitly approved skipping synthetic fresh-agent RED/GREEN runs because results vary materially by model and runtime. This is recorded as a project decision rather than fabricated evidence.

The committed behavioral scenarios remain under:

```text
skills/developing-filament-packages/tests/
```

They are retained for future regression testing when a suitable multi-agent harness is available. Standard v1 instead treats real package use as the empirical feedback loop for future Skill refinements.

## Pending repository state

The implementation and cross-repository pilots are complete. Remaining work requires repository settings or release actions not exposed by the connected GitHub tooling:

- enable `mortalkiller/filament-package-template` as a GitHub Template Repository;
- configure/review branch protection or rulesets for `main` and maintained `*.x` branches;
- set `main` as the default branch for the existing package repositories;
- publish a new stable `filament-page-header` patch release from the current stable `main` state so PlumbPHP can evaluate the SHA-pinned workflow state;
- confirm the resulting `filament-page-header` PlumbPHP Security and Composite scores reach 100.

## Final pre-merge verification

Verified after the Skill, installer, Standard documentation update, initializer recovery behavior, compatibility matrix, and checker hardening were all present together:

- Package tests: https://github.com/mortalkiller/filament-package-template/actions/runs/35636909739 — success.
  - PHP 8.3 / Testbench ^11.0 / Filament 5.8.1 exact minimum.
  - PHP 8.5 / Testbench ^11.0 / latest resolved Filament ^5.8.1.
  - Skill static contract.
  - Skill installer behavior.
  - initializer smoke/recovery behavior.
  - package-standard checker fixture suite.
- Code quality: https://github.com/mortalkiller/filament-package-template/actions/runs/35636909741 — success.
- Documentation: https://github.com/mortalkiller/filament-package-template/actions/runs/35636909721 — success; production deploy skipped on pull request.
- Package standard: https://github.com/mortalkiller/filament-package-template/actions/runs/35636909724 — success.

The Skill's synthetic fresh-agent RED/GREEN phase was intentionally waived by the maintainer and is not represented as executed evidence.
