# MortalKiller Filament Package Standard v1

**Status:** Approved — Standard v1  
**Owner:** Pedro Monteiro / MortalKiller  
**Scope:** Public Filament packages maintained under the `mortalkiller` GitHub account.

## Purpose

This standard makes MortalKiller Filament packages consistent, maintainable, well documented, safe to publish, and predictable to release. It defines minimums rather than forcing every repository to have identical complexity.

A package without JavaScript does not need Playwright. A package without migrations does not need `database/`. Conditional tooling exists only when the package genuinely needs it.

## Core principles

1. **Native first.** Prefer Laravel and Filament abstractions over package-specific replacements.
2. **Small public API.** Every public method is a maintenance commitment.
3. **Opt in.** Installing a package must not unexpectedly replace native behavior.
4. **Backwards compatible.** Breaking changes belong in major releases.
5. **Tested behavior.** Tests prove public behavior and compatibility claims.
6. **Documentation is part of the feature.** Public work is incomplete without current docs.
7. **Public by default, private never.** Never publish private consumers, customers, secrets, or real infrastructure.
8. **One current truth.** Code, tests, README, docs, roadmap, issues, and releases must not contradict each other.
9. **Roadmap is future-only.** Completed work leaves the roadmap.
10. **Release hygiene.** CI, docs, compatibility, issues, privacy, and package health are release gates.

## Repository baseline

Required for public packages:

```text
.github/
  ISSUE_TEMPLATE/
  workflows/
docs/
  roadmap.md
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

Conditional directories such as `config/`, `database/`, `resources/`, `workbench/`, `bin/`, root `package.json`, Playwright, and PHPStan configuration are created only when useful.

## Branching and versioning

- `main` represents the latest stable public state and is the production documentation source.
- `1.x`, `2.x`, `3.x`, etc. are major-version development/support lines.
- New packages develop the first major on `1.x`; production docs remain disabled until the release-ready v1 state is merged to `main`.
- Use temporary branches such as `feature/*`, `fix/*`, `docs/*`, `test/*`, `refactor/*`, and `chore/*`.
- Use Semantic Versioning and tags only in the form `vX.Y.Z`.
- Fix the oldest supported affected line first and forward-port when practical.

## Public API

A MortalKiller package API should feel natural to Laravel/Filament developers.

Prefer:

- `::make()` where it matches Filament conventions;
- fluent methods returning `static`;
- typed callbacks for advanced nested configuration;
- enums for finite public choices;
- one canonical method instead of aliases;
- closures only when dynamic runtime context has a real use case;
- config files for structural/infrastructure defaults;
- fluent plugin APIs for panel/runtime behavior;
- contracts only where consumers genuinely need substitution.

Before adding public API, ask:

- Does Laravel already solve this?
- Does Filament already solve this?
- Can a native component be reused?
- Is the method name predictable?
- Do we need the option now?
- Should a finite choice be an enum?
- Does this belong in config or the fluent API?
- Is a contract genuinely required?
- Can the API survive likely framework evolution?

Invalid configuration should fail early with actionable messages. Public APIs remain compatible throughout a stable major; deprecated replacements are documented before removal.

## Migrations

Package-owned tables may be managed by the package.

When touching application-owned structures such as `users` or `personal_access_tokens`, migrations must be defensive. Do not remove columns or data that predated the package.

## Testing and CI

Every package must validate:

- Composer metadata;
- code style with Laravel Pint;
- tracked PHP syntax;
- automated PHP behavior;
- declared compatibility boundaries;
- documentation build.

PHPUnit and Pest are both valid.

PHPStan is the default for new packages unless there is a deliberate reason not to use it. JavaScript tests, Playwright, and a workbench are conditional on browser/JavaScript behavior.

CI must exercise every declared compatibility boundary, including meaningful minimum and latest-allowed dependency combinations. Security-sensitive features require negative-path coverage.

A required CI failure means **no merge**.

Reusable workflows are hosted in `mortalkiller/filament-package-template` and consumed from the Standard v1 support line `@1.x`.

## Documentation

Every public package uses Astro + Starlight under `docs-site/` and publishes latest-stable documentation to:

```text
https://docs.pedromonteiro.dev/<package-name>/
```

The documentation should contain these conceptual areas:

- Getting Started
- Guides
- API Reference
- Development
- Project

The README is the quick start. Starlight is the complete user documentation. Source code and tests are the runtime authority.

API reference is derived from the current public source and tests, not only README prose. Do not document APIs that do not exist.

All documentation sites use the shared Pedro Monteiro branding, link to GitHub and `https://pedromonteiro.dev`, and validate subpath-safe favicons, manifests, images, links, and edit URLs.

Pull requests build docs but do not deploy them. Production deployment occurs only from `main`, through the `docs-production` environment, and only when `DOCS_DEPLOY_ENABLED=true`.

## Privacy

Public repositories must not contain:

- customer names or data;
- private application/project names;
- private domains;
- production IPs;
- SSH details;
- real internal container names;
- private filesystem/infrastructure paths;
- credentials, API keys, tokens, session IDs, MFA secrets, or recovery codes;
- private screenshots.

Use fictional examples such as `Customer`, `Order`, `Product`, `Workspace`, `DemoUser`, `demo-filament-app`, `example.com`, `/var/www/app`, and `php`.

Visibility is not authorization. Security-sensitive decisions remain server-side.

## GitHub workflow

Meaningful features, significant bugs, public API work, and security-sensitive changes should have GitHub Issues with objective acceptance criteria.

Repositories provide standard bug/feature issue forms and a pull-request template.

Use focused PRs and lightweight Conventional Commit prefixes such as:

```text
feat:
fix:
docs:
test:
refactor:
chore:
ci:
style:
```

Squash merge is the default.

Lifecycle:

```text
Roadmap -> Issue -> Branch -> PR -> CI -> Merge -> Roadmap cleanup -> Release -> Issue closing record
```

Closed feature issues should record implementation, documentation, and release information when available.

## Dependencies and maintenance

Every runtime dependency is a maintenance commitment.

Prefer framework-native solutions. Keep runtime dependencies in `require`, development tooling in `require-dev`, and optional feature dependencies optional when practical using Composer `suggest` plus clear diagnostics.

Declared compatibility must match CI evidence.

Dependabot baseline:

- Composer weekly;
- GitHub Actions weekly;
- npm weekly only where Node dependencies exist;
- seven-day cooldown;
- limited open PRs;
- no universal auto-merge.

Prefer GitHub Actions pinned by full commit SHA.

Packages clearly communicate whether each major is Active, Maintenance, Security-only, Unsupported, or Archived.

## Security

Use GitHub private vulnerability reporting/security advisories. Do not disclose unpatched vulnerabilities in public issues or pull requests.

Claims such as `fail closed`, tenant isolation, authorization, and protection require tests.

Raise review depth for authentication, authorization, MFA, passwords, tokens, sessions, tenant isolation, middleware, uploads, raw HTML, external URLs, and credentials.

Escape HTML by default. Trusted raw HTML requires explicit opt-in.

## PlumbPHP quality gate

Every actively maintained MortalKiller public package must achieve and maintain:

```text
Ecosystem    100
Maintenance  100
Security     100
Composite    100
```

README files expose the relevant PlumbPHP badges.

Before a release is considered complete, verify the current PlumbPHP scan at https://plumbphp.dev/. If reliable API automation is unavailable, record manual verification.

When a score is below 100:

1. identify the exact finding;
2. decide whether it is technically valid;
3. fix legitimate repository issues;
4. rescan;
5. confirm 100.

Never weaken security, compatibility, tests, or architecture merely to satisfy a scanner.

## Definition of Done

Before calling package work complete, verify all applicable items:

### Implementation
- Public API follows native-first, fluent, minimal conventions.
- Backwards compatibility is respected inside the major.
- Tests cover behavior proportional to risk.

### Quality
- Composer validation passes.
- Pint passes.
- PHP syntax passes.
- PHP tests pass.
- PHPStan passes when enabled.
- Minimum/latest compatibility evidence passes.
- JS/browser tests pass when applicable.
- Docs build passes.

### Documentation
- README quick start is current.
- Guides and API reference match source.
- Compatibility matches Composer.
- Migration guide is current when required.
- Roadmap contains future work only.
- Security reporting is documented.
- Branding/base path/internal links are valid.

### Privacy
- No private consumer/customer/infrastructure information.
- No secrets or credentials.
- Screenshots and fixtures use fictional data.

### GitHub
- Meaningful work has an issue.
- Acceptance criteria are objective.
- PR is focused and references the issue.
- Required CI is green.
- Completed roadmap work is removed.
- Completed issues get a useful closing record.

### Release
- Correct release state is verified.
- Previous tag to release commit is inspected.
- Docs and compatibility are current.
- Upgrade steps are included when required.
- Release notes are evidence-based.
- Tag is `vX.Y.Z`.
- PlumbPHP is 100 in all required categories.

## Template and agent tooling

The canonical template repository is:

```text
mortalkiller/filament-package-template
```

It owns:

- repository skeleton;
- issue/PR templates;
- shared docs branding;
- reusable GitHub Actions;
- automated objective standard checks;
- release policy;
- the installable `developing-filament-packages` skill.

The skill teaches workflow and judgment; this standard and the template remain the structural source of truth.


## Installing the agent skill

Install the cross-runtime skill from this repository:

```bash
bash skills/developing-filament-packages/install.sh ~/.agents/skills
```

Claude Code-compatible location:

```bash
bash skills/developing-filament-packages/install.sh ~/.claude/skills
```

Use `--force` only when deliberately replacing an existing installation.

Synthetic fresh-agent RED/GREEN testing is not a Standard v1 release gate. The maintainer explicitly chose runtime/model-independent static validation plus empirical refinement during real package work. The committed behavioral scenarios remain available for future regression testing when a suitable multi-agent harness exists.
