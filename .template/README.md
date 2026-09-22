# Initializing a new package

Generate a repository from template `2.x`. The generated package itself starts on `1.x`; template and package majors are independent.

Run:

```bash
php .template/initialize-package.php \
  --slug=filament-example \
  --title="Filament Example" \
  --namespace='MortalKiller\FilamentExample' \
  --description="Example Filament package." \
  --type=plugin \
  --workflow-ref=FULL_VALIDATED_TEMPLATE_COMMIT_SHA
```

The workflow ref must be a reviewed full 40-character SHA from `2.x` with successful CI. Mutable refs are rejected.

Profiles: `plugin`, `theme`, `forms`, `tables`, `library`.

Optional capabilities:

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

`theme` implies assets and `browser-tests` implies Workbench. Partial profiles only receive full `filament/filament` in development dependencies when Workbench needs a complete demo panel.

The initializer rewrites package identity, selects the runtime Filament dependency, creates the Package Tools/provider scaffold, materializes selected capabilities, pins shared workflows to the validated SHA, and removes template-only files. Interrupted initialization resumes only when the complete saved profile/capability state matches.

Generated packages consume `mortalkiller/filament-package-standard:^1.0` directly in `require-dev`. That maintainer dependency currently requires PHP 8.3+, while runtime compatibility may still include PHP 8.2.

After initialization, run CI and configure branch/tag protection and documentation deployment before feature work.
