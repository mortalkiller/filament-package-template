# Filament Package Template

Canonical repository template for creating and maintaining MortalKiller Filament packages.

> **Template v2:** the `2.x` line adds runtime-aware Filament package profiles, conditional scaffolding and stronger compatibility/security checks. Generated packages still start their own lifecycle on `1.x`; the template major does not determine the package major.

This repository and the package standard have separate responsibilities:

```text
mortalkiller/filament-package-template
→ creates and bootstraps a package
→ owns the repository skeleton, initializer, reusable workflows, docs infrastructure and mechanical checks

mortalkiller/filament-package-standard
→ defines how packages are developed and released
→ owns the developing-filament-packages maintainer skill
```

## Create a new package

### 1. Create the repository from this template

On GitHub, use **Use this template → Create a new repository**.

Name the repository using the package slug, for example:

```text
mortalkiller/filament-example
```

New packages use `1.x` as their first permanent major branch. Do not introduce a separate `main` or stable-promotion branch.

Clone the generated repository and work on `1.x`:

```bash
git clone git@github.com:mortalkiller/filament-example.git
cd filament-example
git checkout 1.x
```

### 2. Choose a validated template commit

The initializer pins all shared workflows and tooling to an immutable template commit.

In `mortalkiller/filament-package-template`, choose a full 40-character commit SHA from `2.x` for which these workflows are green:

- Package tests
- Code quality
- Documentation
- Package standard
- Zizmor

Do not use `2.x`, `HEAD`, or another mutable reference as the workflow reference.

### 3. Run the initializer

From the newly generated repository:

```bash
php .template/initialize-package.php \
  --slug=filament-example \
  --title="Filament Example" \
  --namespace='MortalKiller\FilamentExample' \
  --description="Example Filament package." \
  --type=plugin \
  --workflow-ref=FULL_VALIDATED_TEMPLATE_COMMIT_SHA
```

### Package profiles and capabilities

The default profile is `plugin`. Available profiles are `plugin`, `theme`, `forms`, `tables`, and `library`. The first two use `filament/filament`; the others use the narrowest matching Filament package and do not generate a panel plugin class.

Optional capabilities are:

```text
--with-config
--with-database
--with-views
--with-translations
--with-stubs
--with-assets
--with-workbench
--with-browser-tests
--with-rector
```

`theme` implies assets. Browser tests imply Workbench. Workbench on a partial profile adds full `filament/filament` only to development dependencies.

The initializer is a one-time operation. It:

- rewrites the Composer package identity;
- updates the namespace, Package Tools service provider and profile-specific Filament scaffold;
- rewrites README, documentation configuration, URLs and repository metadata;
- converts local reusable workflows into SHA-pinned calls to this template;
- pins package-standard and release tooling to the same validated SHA;
- adds `mortalkiller/filament-package-standard:^2.0` to `require-dev`;
- generates package-specific `AGENTS.md` and `CONTRIBUTING.md`;
- removes template-only tooling and tests;
- materializes only the selected optional capabilities.

See [.template/README.md](.template/README.md) for initializer-specific details.

### 4. Install dependencies

```bash
composer install
```

The maintainer skill is then available locally at:

```text
vendor/mortalkiller/filament-package-standard/
└── resources/boost/skills/developing-filament-packages/
    ├── SKILL.md
    └── references/
```

Agents working directly in a standalone package repository should read the installed `SKILL.md` through the generated `AGENTS.md`.

If dependencies are not installed, `AGENTS.md` also links to the canonical skill in the Standard repository.

In a real Laravel application where both `mortalkiller/filament-package-standard` and Laravel Boost are direct dependencies, Boost can discover the same skill through its native third-party skill convention.

### 5. Push the initialized package

Review the generated diff, then:

```bash
git add .
git commit -m "chore: initialize package"
git push origin 1.x
```

Do not continue with feature work until the initial CI is green.

## Repository setup

After initialization, configure the new GitHub repository:

- set `1.x` as the default branch;
- protect the maintained major branch from deletion and force-pushes;
- require pull requests and relevant status checks where appropriate;
- enable automatic deletion of merged temporary branches;
- protect release tags such as `v*` from mutation where available;
- configure the `docs-production` environment and required documentation deployment variables/secrets;
- enable the appropriate Dependabot targets.

Permanent branches are package-major lines only:

```text
1.x
2.x
3.x
...
```

Unsupported historical majors remain frozen rather than being deleted or merged wholesale into newer majors.

## Development flow

Normal work never needs a stable-promotion branch.

```text
Roadmap / requirement
        ↓
GitHub Issue + acceptance criteria
        ↓
feature/* / fix/* / docs/* / test/* / refactor/* / chore/*
        ↓
Pull Request → affected N.x branch
        ↓
CI + review
        ↓
squash merge
        ↓
delete temporary branch
```

Example:

```bash
git checkout 1.x
git pull
git checkout -b feature/action-position
```

Then open the PR back to `1.x`.

Meaningful features, significant bugs, public API changes and security-sensitive work should be tied to an issue with objective acceptance criteria.

The canonical rules live in:

- [Package Standard v2](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md)
- [Development and release flow](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/development-flow.md)
- [Release checklist](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/releasing.md)

## Release flow

A branch may contain unreleased work. A release tag identifies the exact published version.

```text
verified commit on N.x
        ↓
choose SemVer
        ↓
immutable vX.Y.Z tag
        ↓
GitHub Release
        ↓
Packagist
        ↓
release documentation
        ↓
/N.x/ + Latest when applicable
```

Use Semantic Versioning:

```text
compatible fix       → PATCH  → v1.0.1
compatible feature   → MINOR  → v1.1.0
breaking change      → MAJOR  → 2.x / v2.0.0
```

Package majors are independent from Filament majors.

Before a release:

- inspect the diff from the previous tag on that major;
- verify the exact release commit has successful required CI;
- verify compatibility boundaries and applicable browser/JavaScript checks;
- verify README/docs/API references against source and tests;
- remove completed roadmap items;
- audit public content for private data or infrastructure;
- verify PlumbPHP results according to the Standard;
- prepare evidence-based release notes.

Stable releases publish documentation from the exact release tag. PR and branch-push documentation workflows validate only; they do not publish stable documentation.

## Published tags are immutable

Never delete and recreate a version tag after it has been published or observed by Packagist.

Wrong:

```text
v1.0.0
  ↓
delete tag
  ↓
recreate v1.0.0 on another commit
```

Correct:

```text
v1.0.0 contains a problem
        ↓
fix the problem
        ↓
publish v1.0.1
```

Packagist versions are immutable once observed. A bad release may be withdrawn when appropriate, but its version must not be reused for different source code.

## Documentation

Package documentation is built with the shared template infrastructure.

The canonical package URL remains:

```text
https://docs.pedromonteiro.dev/<package>/
```

Major channels are published under:

```text
https://docs.pedromonteiro.dev/<package>/1.x/
https://docs.pedromonteiro.dev/<package>/2.x/
```

Only the semantically newest stable release may update Latest. Older-major releases and stale workflow reruns must not downgrade it.

Template documentation:

https://docs.pedromonteiro.dev/filament-package-template/

## Quick reference

For a brand-new package:

```text
Use this template
    ↓
create repository
    ↓
1.x
    ↓
run initialize-package.php with a validated template SHA
    ↓
composer install
    ↓
review + initial commit
    ↓
configure GitHub repository
    ↓
CI green
    ↓
Issue → temporary branch → PR → 1.x
    ↓
verified commit → immutable vX.Y.Z → GitHub Release → Packagist → docs
```
