# Release checklist

- [ ] Identify the package major, its `N.x` branch and exact release commit; do not introduce a stable-promotion branch.
- [ ] Inspect the previous tag on this major → release commit diff and choose PATCH, MINOR or MAJOR according to compatibility.
- [ ] Verify successful push CI on the exact commit: Composer validation, tests, Pint/quality, tracked PHP syntax and package standard.
- [ ] Verify minimum/latest compatibility, PHPStan when enabled, and JavaScript/browser tests when applicable.
- [ ] Build documentation and verify README, guides, API reference, compatibility and migration instructions against source and tests.
- [ ] Remove completed roadmap work and record issue acceptance criteria.
- [ ] Audit public content for private customers, infrastructure and secrets.
- [ ] Prepare evidence-based release notes and any required upgrade instructions.
- [ ] Verify the current PlumbPHP scan and all four 100 scores; record the scanned ref and disclose stale/unavailable results.
- [ ] Create a new immutable `vX.Y.Z` tag on a verified commit belonging to `X.x`, then publish the GitHub Release. Do not move tags.
- [ ] Mark RC/beta releases as prereleases; never promote them to stable documentation.
- [ ] Verify the release-documentation workflow: source equals the tag, exact-commit CI passed, and major/Latest channels cannot regress.
- [ ] Confirm `docs-production` allows release tags, and `DOCS_REMOTE_PATH` identifies only this package.
- [ ] Check the public major channel, canonical Latest URL, version selector, assets and links. Other majors must remain available.
- [ ] Change the default branch only after the first stable release of a newer major is complete.
- [ ] Record the published release on relevant issues and update installed agent skills when tooling changed.

If publication needs a re-run, use Release documentation with the existing tag; dry-run defaults to true. A tag predating the migration must not be moved or silently rebuilt using newer branch source. Publish a new release containing the configuration.

A release is not complete merely because CI is green. Resolve legitimate Plumb findings without weakening security or compatibility; distinguish successful code validation from an unexecuted live deployment.
