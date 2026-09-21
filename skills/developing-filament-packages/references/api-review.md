# Public API review

Before adding or changing public package API, answer these questions against the current repository:

- Does Laravel already solve this?
- Does Filament already solve this?
- Can an existing native component, Action, schema, auth provider, navigation primitive, notification, middleware, or storage abstraction be reused?
- Is the proposed method name predictable to a Laravel/Filament developer?
- Is this option required by a real use case now?
- Should a finite public choice be an enum instead of a magic string?
- Does this belong in runtime/fluent configuration or structural config?
- Is Closure support genuinely useful for runtime context?
- Is a contract needed because consumers require substitution, or only for architectural symmetry?
- Can the API survive likely Laravel/Filament evolution?
- What backwards-compatibility burden does this create inside the current major?
- How will invalid configuration fail early and clearly?

Prefer one canonical API over aliases.

Prefer intent-oriented names:

```php
->tenantScoped()
->subNavigation()
->compactBelow(1024)
```

Avoid names that expose internal implementation details.

If replacing stable API, preserve a deprecated compatibility path where practical and document migration before removal in a future major.
