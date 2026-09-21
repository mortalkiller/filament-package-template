# Contributing

## Branches

- `main` represents the latest stable public state.
- `1.x`, `2.x`, `3.x`, and later `*.x` branches are major-version development/support lines.
- Use focused temporary branches such as `feature/*`, `fix/*`, `docs/*`, `test/*`, `refactor/*`, or `chore/*`.

For a brand-new package, create `1.x` from the initialized `main` baseline and perform first-major development on `1.x`. Keep production documentation deployment disabled until the first stable v1 state is merged to `main`.

## Local checks

```bash
composer install
composer check
```

If the package owns JavaScript or browser behavior, run the package-specific JavaScript/browser checks documented in its development guide.

## Pull requests

Keep pull requests focused. Meaningful public API or behavior changes should reference a GitHub issue with objective acceptance criteria. Update tests and documentation in the same work when they are part of the public behavior.

Public API should remain Laravel/Filament-native-first, small, fluent, and backwards compatible inside a major.

Do not publish credentials, customer information, private application names, internal URLs, production infrastructure, or private filesystem paths in code, tests, screenshots, issues, or documentation.
