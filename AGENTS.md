# Agent instructions

This repository follows MortalKiller Filament Package Standard v1.

Canonical standard: https://github.com/mortalkiller/filament-package-standard/blob/1.x/docs/package-standard.md
Canonical skill: https://github.com/mortalkiller/filament-package-standard/blob/1.x/resources/boost/skills/developing-filament-packages/SKILL.md

Read the canonical standard and skill before package-template work. Permanent branches are major lines; temporary branches and PRs target the affected major. Releases are immutable tags on verified major commits, without a promotion branch.

Run the required CI-equivalent checks and audit public content for private consumer, customer, infrastructure and credential information. Do not claim a release or live documentation deployment was verified when only fixtures or a build ran.

After `composer install`, read and use `vendor/mortalkiller/filament-package-standard/resources/boost/skills/developing-filament-packages/SKILL.md`. If dependencies are not installed, use the canonical skill URL above.
