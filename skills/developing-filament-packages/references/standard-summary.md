# MortalKiller Filament Package Standard v1 — quick reference

The canonical complete standard is `docs/package-standard.md` in `mortalkiller/filament-package-template`.

## Branches

- `main`: latest stable public state and production-docs source.
- `1.x`, `2.x`, `3.x`: major development/support lines.
- Temporary work: `feature/*`, `fix/*`, `docs/*`, `test/*`, `refactor/*`, `chore/*`.
- New packages develop v1 on `1.x`; merge release-ready stable state to `main`.

## Repository baseline

Expected public package files include:

```text
.github/ISSUE_TEMPLATE/
.github/workflows/
docs/roadmap.md
docs-site/
src/
tests/
AGENTS.md
CONTRIBUTING.md
LICENSE.md
README.md
SECURITY.md
composer.json
phpunit.xml.dist
```

Config, migrations, resources, workbench, JavaScript, Playwright and PHPStan are conditional on real package needs.

## CI

Required baseline:

- Composer validation
- Pint
- PHP syntax
- PHP tests
- declared compatibility boundaries
- Starlight docs build

PHPStan is expected for new packages. JS/browser tests are conditional.

No required-check failure is merged.

## Documentation

Public docs:

```text
https://docs.pedromonteiro.dev/<package>/
```

Conceptual sections:

- Getting Started
- Guides
- API Reference
- Development
- Project

README is quick start; Starlight is complete docs; source/tests are runtime authority.

Production docs deploy from `main` only. PRs build but never deploy.

## Privacy

Never publish:

- customers/private applications;
- production hosts/IPs/SSH details;
- real internal container names or host paths;
- credentials/tokens/session/MFA secrets;
- private screenshots or personal data.

Use synthetic examples such as `Customer`, `Order`, `Product`, `Workspace`, `demo-filament-app`, `example.com`, `/var/www/app`, and `php`.

## GitHub lifecycle

```text
Roadmap -> Issue -> Branch -> PR -> CI -> Merge -> Roadmap cleanup -> Release -> Issue closing record
```

Squash merge is the default.

## Maintenance

- Dependabot weekly for Composer/GitHub Actions; npm when applicable.
- Prefer SHA-pinned GitHub Actions.
- Declared compatibility must be tested compatibility.
- Actively maintained public packages require PlumbPHP 100 in Ecosystem, Maintenance, Security and Composite before release completion.
