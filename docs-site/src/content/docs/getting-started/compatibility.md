---
title: Compatibility
description: Runtime and development compatibility for template v2.
---

Template v2 declares PHP `^8.2`, Laravel 12 or 13, and Filament `^5.8.1`. CI covers the minimum runtime boundary, current Laravel 12 and 13 combinations, PHP through 8.5, and Windows.

`plugin` and `theme` depend on `filament/filament`. `forms`, `tables`, and `library` use the narrowest corresponding Filament package. Partial profiles only add full `filament/filament` as a development dependency when Workbench is selected.

The maintainer dependency `mortalkiller/filament-package-standard:^2.0` supports PHP 8.2+, so the minimum-runtime CI now verifies the real generated dependency set without removing maintainer tooling.

Generated packages must keep declared compatibility aligned with actual CI evidence.
