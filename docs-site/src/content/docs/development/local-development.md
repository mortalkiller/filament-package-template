---
title: Local development
description: Develop generated packages with optional Testbench Workbench support.
---

PHP tests use Orchestra Testbench. Select `--with-workbench` when a package benefits from an interactive representative Filament panel.

Workbench generation adds `testbench.yaml`, a demo panel provider, Workbench autoloading, and:

```bash
composer serve
```

Select `--with-browser-tests` for browser-visible behavior. It implies Workbench and adds Playwright, a smoke test, and the shared browser workflow.

For a separate consuming Laravel application, a Composer path repository with `symlink: true` remains the preferred workflow for unreleased package changes.

Use generic examples only; never publish real customer applications, production paths, infrastructure details or credentials.
