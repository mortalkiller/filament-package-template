# Releasing a MortalKiller Filament package

Read [Development and release flow](development-flow.md). Release directly from the affected `N.x` branch, without a promotion merge to another permanent branch.

## Release readiness

- [ ] Select the major and exact commit; inspect its diff from the previous release on that major.
- [ ] Composer validation, PHP tests, Pint, tracked syntax and package-standard checks pass on that exact commit.
- [ ] Minimum/latest compatibility passes, with PHPStan and JavaScript/browser tests when applicable.
- [ ] Documentation builds, public API and upgrade instructions match source, and the README is current.
- [ ] Remove completed roadmap entries and record issue acceptance criteria.
- [ ] Audit public content for private customers, infrastructure and secrets.
- [ ] Prepare release notes from repository evidence.
- [ ] Verify PlumbPHP Ecosystem 100.
- [ ] Verify PlumbPHP Maintenance 100.
- [ ] Verify PlumbPHP Security 100.
- [ ] Verify PlumbPHP Composite 100.

Record the scan time and ref; a stale scan does not prove a new commit passed. Resolve legitimate findings without weakening security, compatibility, tests or architecture.

## Tags and publication

Create a new immutable `vX.Y.Z` tag on the verified commit in `X.x`, then publish the GitHub Release. PATCH is a compatible fix, MINOR is compatible functionality, and MAJOR requires an incompatible-change review and migration guide. Do not move, delete and recreate, or reuse published version tags. Clearly mark RC/beta releases as prereleases.

Packagist versions are immutable once observed. If a published version is wrong, do not retag the same version to another commit: withdraw/soft-delete it when appropriate, correct the code, and publish a new semantic version.

The Release documentation workflow uses the exact tag, checks major ancestry and successful exact-commit push CI, then publishes the major channel through `docs-production`. Only the highest stable semantic version can update Latest; old-major patches, prereleases and stale reruns must not overwrite newer docs. Other version directories are preserved.

Verify the public major channel, canonical Latest URL, selector, assets and links after deployment. Manual re-publication accepts an existing stable tag and defaults to a dry run. A tag predating versioned documentation must not be moved or built from newer branch source; use a new release on a supported line.

Record the release and verification on the relevant issues. After the first stable release of a newer major is complete, update the default branch. A tag or a green CI run alone does not prove successful live publication.

## Release notes

Use a short introduction, new functionality, fixes/improvements, significant documentation changes, upgrade steps, compatibility and full changelog. Include only changes verified in the release diff.
