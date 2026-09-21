# Initialize a new package

After creating a repository from this GitHub template, run:

```bash
php .template/initialize-package.php \
    --slug=filament-example \
    --title="Filament Example" \
    --namespace='MortalKiller\FilamentExample' \
    --description="Short package description."
```

The initializer updates package metadata, namespace, documentation URLs/base path, and service-provider naming. It then removes template-only infrastructure while retaining the thin reusable-workflow wrappers needed by the generated package.

The command is intentionally one-shot and refuses to initialize an already initialized repository.
