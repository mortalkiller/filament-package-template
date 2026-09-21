# Releasing a MortalKiller Filament package

Use this checklist before publishing any stable package release.

## Release readiness

- [ ] Release branch/state is correct.
- [ ] Compare the previous tag to the release commit and inspect actual user-visible changes.
- [ ] Composer validation passes.
- [ ] PHP tests pass.
- [ ] Code quality passes.
- [ ] Compatibility matrix passes.
- [ ] JavaScript/browser tests pass when applicable.
- [ ] Documentation build passes.
- [ ] README and public docs are current.
- [ ] Roadmap contains future work only.
- [ ] Relevant issues have objective acceptance criteria completed and appropriate closing records.
- [ ] Public-content privacy audit passes.
- [ ] Upgrade instructions are included when users must take action.
- [ ] Release tag follows `vX.Y.Z`.
- [ ] GitHub Release notes are prepared from repository evidence, not memory.
- [ ] PlumbPHP Ecosystem 100.
- [ ] PlumbPHP Maintenance 100.
- [ ] PlumbPHP Security 100.
- [ ] PlumbPHP Composite 100.

## PlumbPHP gate

Actively maintained public packages must reach 100 in all four required PlumbPHP categories before a release is considered complete.

If an automated PlumbPHP API integration is not available or reliable, verify the current public scan manually at https://plumbphp.dev/ and record the result in release verification notes.

If PlumbPHP reports less than 100, investigate the specific finding and fix legitimate repository issues. Do not weaken security, compatibility, tests, or architecture merely to satisfy a scanner; surface genuine conflicts for an explicit maintainer decision.

## Tagging

Use only:

```text
vX.Y.Z
```

The tag must point at the verified stable release commit.

## Release notes

Preferred structure:

```text
Short introduction
What's new
Fixes / improvements
Documentation, when significant
Upgrade
Compatibility
Full changelog
```

Breaking changes require a major release and a migration guide.
