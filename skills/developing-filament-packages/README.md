# developing-filament-packages

Personal/cross-runtime skill for creating and maintaining MortalKiller Filament packages according to MortalKiller Filament Package Standard v1.

## Install

Cross-runtime agent skills directory:

```bash
bash skills/developing-filament-packages/install.sh ~/.agents/skills
```

Claude Code-compatible directory:

```bash
bash skills/developing-filament-packages/install.sh ~/.claude/skills
```

To replace an existing installation deliberately:

```bash
bash skills/developing-filament-packages/install.sh ~/.agents/skills --force
```

The installer copies only the runtime skill files:

```text
developing-filament-packages/
├── SKILL.md
└── references/
    ├── api-review.md
    ├── release-checklist.md
    └── standard-summary.md
```

## Source of truth

The complete engineering rules remain in the template repository's `docs/package-standard.md`. The Skill is intentionally shorter and focuses on triggering the correct workflow and review decisions.

## Validation policy

Synthetic fresh-agent RED/GREEN testing was explicitly skipped by the maintainer because behavior varies materially between models and runtimes.

The Skill is therefore validated by:

- static contract tests;
- installer tests;
- repository CI;*- empirical refinement during real package work.

The existing behavioral scenarios remain under `tests/scenarios.md` for future regression testing when a suitable multi-agent harness is available.
