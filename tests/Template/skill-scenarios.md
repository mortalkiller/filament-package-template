# developing-filament-packages behavioral scenarios

These scenarios are retained for future empirical regression testing in fresh agent contexts. Compare behavior without the skill against behavior after Laravel Boost (or the runtime under test) has synchronized the skill.

## Scenario 1 — New package

Prompt:

> Create a new Filament package for reusable panel announcements. Make it production-ready and publishable.

Baseline failures to detect:

- ignores the canonical package template/standard;
- omits docs/CI/release structure;
- forces unnecessary tooling;
- invents framework abstractions instead of using Laravel/Filament-native concepts.

With-skill success:

- starts from the MortalKiller package standard/template;
- keeps conditional tooling conditional;
- plans docs, tests, CI, privacy, and release readiness.

## Scenario 2 — Public API feature

Prompt:

> Add configurable action positioning to filament-page-header. Implement it and tell me when it is done.

Baseline failures to detect:

- adds public API without checking Laravel/Filament-native options;
- no issue/acceptance criteria;
- no compatibility or documentation review;
- calls the task complete based only on implementation.

With-skill success:

- reviews native alternatives and public API cost;
- binds meaningful work to issue/acceptance criteria;
- treats tests/docs/compatibility as part of the feature.

## Scenario 3 — Public documentation privacy

Prompt:

> Document local development using the Docker commands from my real application because they already work.

Baseline failures to detect:

- copies private application names;
- publishes real container names, paths, hosts, ports, or infrastructure conventions.

With-skill success:

- rejects private examples for public docs;
- substitutes synthetic examples such as `demo-filament-app`, `php`, and `/var/www/app`;
- includes a privacy audit.

## Scenario 4 — Release readiness

Prompt:

> Everything looks green. Prepare and publish the next package release now.

Baseline failures to detect:

- writes release notes from memory;
- does not inspect previous tag → release state;
- ignores roadmap/issues/privacy;
- does not verify PlumbPHP 100.

With-skill success:

- inspects repository evidence;
- verifies CI/docs/compatibility/roadmap/issues/privacy;
- blocks release-complete status until PlumbPHP Ecosystem, Maintenance, Security, and Composite all equal 100.

## Scenario 5 — Roadmap maintenance

Prompt:

> Update the roadmap after we finished the generator and navigation features.

Baseline failure to detect:

- marks completed items as complete but leaves them in the roadmap.

With-skill success:

- removes completed work entirely;
- keeps historical evidence in Releases, issues, PRs, and Git history.

## Scoring

A scenario passes only when all listed with-skill success behaviors are present and none of the baseline failure behaviors remain.
