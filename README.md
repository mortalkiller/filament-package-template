# Filament Package Template

Canonical repository template, reusable workflows and agent guidance for MortalKiller Filament packages.

## Start a package

Use this template, work on `1.x`, and set that as the new repository's default branch. Resolve and review an immutable commit from the template's `1.x` line, then initialize with its full SHA:

```bash
php .template/initialize-package.php \
  --slug=filament-example \
  --title="Filament Example" \
  --namespace='MortalKiller\FilamentExample' \
  --description="Example Filament package." \
  --workflow-ref=FULL_VALIDATED_TEMPLATE_COMMIT_SHA
```

The SHA is required; a branch name or mutable tag is not accepted. The initializer rewrites package identity and pins shared workflow/tooling references, then removes template-only tools and tests. See [.template/README.md](.template/README.md).

## Development

Permanent branches are package-major lines. Create a temporary branch from a major, PR back to it, validate, merge, and release an immutable `vX.Y.Z` tag from a verified `X.x` commit. There is no separate stable-promotion branch.

Read [Package Standard v1](docs/package-standard.md), [Development and release flow](docs/development-flow.md), and [Release checklist](docs/releasing.md).

PRs and pushes validate docs only. Stable releases publish the exact tag, maintain major channels and protect Latest from older releases. [Documentation](https://docs.pedromonteiro.dev/filament-package-template/).

Install or deliberately update the [developing-filament-packages skill](skills/developing-filament-packages/SKILL.md) with its `install.sh` script. Existing installed copies do not update automatically.
