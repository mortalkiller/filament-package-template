# Releasing a MortalKiller Filament package

Read [Development and release flow](development-flow.md). Release directly from the affected package `N.x` branch, without a promotion merge to another permanent branch.

## Release readiness

- [ ] Select the package major and exact commit; inspect its diff from the previous release on that major.
- [ ] Composer validation, PHP tests, Pint, tracked syntax and package-standard checks pass on that exact commit.
- [ ] Strict test-runner behavior is enabled; warnings, risky tests and empty suites cannot silently pass.
- [ ] Minimum/latest compatibility passes, with Larastan and Zizmor where applicable.
- [ ] JavaScript/browser/Workbench tests pass when the package owns that behavior.
- [ ] Documentation builds, public API and upgrade instructions match source, and the README is current.
- [ ] Remove completed roadmap entries and record issue acceptance criteria.
- [ ] Audit public content for private customers, infrastructure and secrets.
- [ ] Prepare release notes from repository evidence.
- [ ] Verify PlumbPHP Ecosystem 100.
- [ ] Verify PlumbPHP Maintenance 100.
- [ ] Verify PlumbPHP Security 100.
- [ ] Verify PlumbPHP Composite 100.

A Standard/tooling upgrade does not determine package SemVer. Choose PATCH/MINOR/MAJOR from the actual consumer-visible release diff.

## Tags and publication

Create a new immutable `vX.Y.Z` tag on the verified commit in `X.x`, then publish the GitHub Release. Do not move, delete and recreate, or reuse published version tags.

Packagist versions are immutable once observed. If a published version is wrong, correct the code and publish a new semantic version rather than retagging the same version.

Release documentation uses the exact tag and must not allow old-major patches, prereleases or stale reruns to overwrite newer docs.

Record the release and verification on relevant issues. Change the default branch only after the first stable release of a newer package major is complete.

## Release notes

Use a short introduction, new functionality, fixes/improvements, significant documentation changes, upgrade steps, compatibility and full changelog. Include only changes verified in the release diff.
