# MortalKiller Filament Package Standard v1 — quick reference

The canonical complete standard is `docs/package-standard.md` in `mortalkiller/filament-package-template`. See `docs/development-flow.md` for the maintainer workflow.

## Branches

Permanent branches are package majors (`1.x`, `2.x`, `3.x`), without a separate stable-promotion branch. The default is the newest stable major, or `1.x` before the first release. Temporary branches start from, and target, the affected major. Release immutable `vX.Y.Z` tags directly from verified commits on `X.x`. Branch heads may include unreleased work. Do not move published tags or merge an older major wholesale into a newer one.

## Repository baseline

Keep `.github/ISSUE_TEMPLATE/`, `.github/workflows/`, `docs/roadmap.md`, `docs-site/`, `src/`, `tests/`, `AGENTS.md`, `CONTRIBUTING.md`, `LICENSE.md`, `README.md`, `SECURITY.md`, `composer.json`, and `phpunit.xml.dist`. Config, migrations, resources, workbench, JavaScript and Playwright exist only when needed. PHPStan is expected for new packages unless deliberately excluded.

## CI

Require Composer validation, Pint, tracked PHP syntax, PHP behavior tests, minimum/latest compatibility boundaries and the Starlight docs build. Include static analysis and JS/browser tests where applicable. No required-check failure is merged. External actions and workflows use full SHA references; the checker and publication tooling use the same validated `standard-ref`.

## Documentation

The README is the quick start; Starlight is the complete guide; source and tests are runtime authority. Cover Getting Started, Guides, API Reference, Development and Project. PRs/pushes validate only. Stable releases publish the exact tag through `docs-production`, after exact-commit CI. `/<package>/` is Latest; `/<package>/N.x/` is the latest published documentation for that major. Prereleases and older majors must not replace Latest. Manual publication defaults to a dry run of an existing stable release. Preserve other major directories and prevent rollback.

## Privacy

Never publish private customers/applications, real infrastructure, hosts/IPs/SSH details, internal container names or paths, credentials/tokens/session/MFA secrets, private screenshots or personal data. Use synthetic examples: `Customer`, `Order`, `Product`, `Workspace`, `demo-filament-app`, `example.com`, `/var/www/app`, `php`.

## GitHub lifecycle

Roadmap → Issue → temporary branch → PR to major → CI/review → squash merge → roadmap cleanup → immutable tag and release → issue closing record. Fix the oldest supported affected line first and forward-port the relevant fix with tests. Historical unsupported lines stay frozen.

## Maintenance

Dependabot runs weekly for Composer and GitHub Actions, and npm where applicable; use the seven-day cooldown and limited PR counts, without universal auto-merge. Declared compatibility must be tested. Actively maintained public packages require fresh PlumbPHP 100 in Ecosystem, Maintenance, Security and Composite before release completion. Never claim a cached scan evaluated a newer commit.
