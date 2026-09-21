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

## Reusable workflow limits still requiring pilot verification

The pull-request runs prove local reusable workflow syntax and behavior inside the template repository.

The following cross-repository behavior still requires the planned package pilots after Standard v1 is merged and the `1.x` support line exists:

- consumer checkout of `mortalkiller/filament-package-template@1.x`;
- `docs-production` environment secrets resolving correctly inside a cross-repository reusable documentation workflow;
- browser workflow parity on `filament-page-header`.

## Agent skill status

The behavioral RED scenarios for `developing-filament-packages` are stored under:

```text
skills/developing-filament-packages/tests/
```

The current ChatGPT harness does not expose fresh-agent/subagent dispatch. The required baseline responses have therefore **not** been fabricated and `SKILL.md` has intentionally not been authored yet.

Standard v1 must not be declared operational until:

1. all baseline scenarios are executed in fresh contexts without the skill;
2. observed failures are recorded;
3. the minimal skill is authored from those failures;
4. the same scenarios pass in fresh contexts with the skill installed;
5. the skill installer is verified.

## Pending repository state

Before Standard v1 is operational:

- complete the agent-skill RED/GREEN cycle;
- merge PR #2 after all acceptance criteria are satisfied;
- create `1.x` from the verified stable `main` state;
- enable the repository as a GitHub Template Repository;
- configure branch protections;
- run cross-repository pilots;
- adopt the standard in the existing package repositories;
- record PlumbPHP 100 evidence for maintained public package releases.
