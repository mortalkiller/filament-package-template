# Release checklist

Before preparing or publishing a release:

- [ ] Confirm the correct stable/version branch and release commit.
- [ ] Inspect the actual previous tag → release-state diff.
- [ ] Verify Composer validation.
- [ ] Verify package tests.
- [ ] Verify Pint/code quality.
- [ ] Verify PHPStan when enabled.
- [ ] Verify declared compatibility boundaries.
- [ ] Verify JavaScript/browser tests when applicable.
- [ ] Verify documentation build.
- [ ] Confirm README, guides, API reference, compatibility and migration docs are current.
- [ ] Remove completed work from the roadmap.
- [ ] Confirm relevant issues/acceptance criteria and closing records.
- [ ] Run the public-content privacy audit.
- [ ] Add upgrade instructions when users must take action.
- [ ] Prepare user-facing release notes from repository evidence.
- [ ] Use a `vX.Y.Z` tag.
- [ ] Verify PlumbPHP Ecosystem 100.
- [ ] Verify PlumbPHP Maintenance 100.
- [ ] Verify PlumbPHP Security 100.
- [ ] Verify PlumbPHP Composite 100.

If PlumbPHP is below 100, identify the exact finding, fix legitimate issues, rescan, and confirm 100. Surface genuine conflicts instead of degrading architecture or security.

A release is not complete merely because CI is green.
