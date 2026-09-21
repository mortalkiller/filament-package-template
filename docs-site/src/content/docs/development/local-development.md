---
title: Local development
description: Develop the package beside a consuming Filament application.
---

Keep the package and consuming application as separate repositories. Use generic examples only:

```text
projects/
  demo-filament-app/
  filament-package-template/
```

A Composer path repository with `symlink: true` is the preferred local workflow for testing unreleased package changes.

Do not publish real customer applications, production paths, container names, SSH details or infrastructure values in this guide.
