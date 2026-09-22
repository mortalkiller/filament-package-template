---
title: Installation
description: Create a Filament package from template v2.
---

Use the GitHub template from the maintained `2.x` line, then run the initializer with a validated immutable template commit.

```bash
php .template/initialize-package.php \
  --slug=filament-example \
  --title="Filament Example" \
  --namespace='MortalKiller\FilamentExample' \
  --description="Example Filament package." \
  --type=plugin \
  --workflow-ref=FULL_VALIDATED_TEMPLATE_COMMIT_SHA
```

Profiles are `plugin`, `theme`, `forms`, `tables`, and `library`. Add config, database, views, translations, stubs, assets, Workbench, browser tests, or Rector only when needed.

A generated package starts on its own `1.x` branch even though it was created from template `2.x`.
