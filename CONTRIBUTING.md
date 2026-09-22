# Contributing

Read [Package Standard v2](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md) and [Development and release flow](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/development-flow.md).

Create a focused temporary branch from the affected package major (`2.x` for this template) and open a PR to that same major. The major-only flow has no separate stable-promotion branch. Use an issue and acceptance criteria for meaningful changes, preserve existing compatibility boundaries, and include tests and documentation. Squash temporary work after review and successful CI.

Run Composer validation, PHP tests, Pint, tracked syntax checks, the package-standard checker, the docs build, and `node --test tools/tests/*.test.mjs` for shared release tooling. Test the initializer and its partial-initialization safety. Do not publish secrets, private applications, customer data or infrastructure in examples or verification logs.

External reusable workflows and their supporting tools must be pinned to the same validated full commit SHA. Keep older consumer contracts compatible during a rollout. Releases use immutable `vX.Y.Z` tags from verified commits on `X.x`; publishing a stable GitHub Release, not pushing a branch, publishes documentation. Never move existing tags.

Report vulnerabilities through GitHub private vulnerability reporting as described in [SECURITY.md](SECURITY.md).

## Agent skill

The repository includes `mortalkiller/filament-package-standard` as a direct development dependency. After `composer install`, read and use:

```text
vendor/mortalkiller/filament-package-standard/resources/boost/skills/developing-filament-packages/SKILL.md
```

In real Laravel applications that install both the Standard and Laravel Boost directly, Boost can discover and synchronize the same skill.
