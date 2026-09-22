---
title: Compatibility
description: Runtime and development compatibility for template v2.
---

Template v2 declares PHP `^8.2`, Laravel 12 or 13, and Filament `^5.8.1`. CI covers the minimum runtime boundary, current Laravel 12 and 13 combinations, PHP through 8.5, and Windows.

`plugin` and `theme` depend on `filament/filament`. `forms`, `tables`, and `library` use the narrowest corresponding Filament package. Partial profiles only add full `filament/filament` as a development dependency when Workbench is selected.

The maintainer dependency `mortalkiller/filament-package-standard:^1.0` currently requires PHP 8.3+. Minimum-runtime CI verifies PHP 8.2 without maintainer-only tooling, so this development constraint does not raise the package runtime requirement.

Generated packages must keep declared compatibility aligned with actual CI evidence.
