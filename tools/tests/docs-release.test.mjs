import test from 'node:test';
import assert from 'node:assert/strict';
import { planRelease, validateRemotePath, requireSuccessfulChecks } from '../docs-release.mjs';

const stable = (tag) => ({ tag_name: tag, draft: false, prerelease: false });
const releases = ['v1.10.0', 'v2.9.0', 'v2.10.0'].map(stable);

test('the latest stable release updates its major and Latest', () => {
  const plan = planRelease(releases, 'v2.10.0');
  assert.equal(plan.major, '2.x');
  assert.equal(plan.publish, true);
  assert.equal(plan.latest, true);
});
test('an older major cannot replace Latest', () => {
  assert.equal(planRelease(releases, 'v1.10.0').latest, false);
});
test('an outdated release cannot downgrade its major', () => {
  assert.equal(planRelease(releases, 'v2.9.0').publish, false);
});
test('release ordering is numeric, not alphabetical or chronological', () => {
  assert.equal(planRelease([...releases].reverse(), 'v2.10.0').latest, true);
});
test('drafts and prereleases cannot be published', () => {
  for (const extra of [{ ...stable('v3.0.0'), draft: true }, { ...stable('v3.0.0'), prerelease: true }]) {
    assert.throws(() => planRelease([...releases, extra], 'v3.0.0'), /published stable release/);
  }
});
test('unpublished or malformed tags are rejected', () => {
  for (const tag of ['v4.0.0', 'v01.0.0', 'main', '../main', 'v2.10.0-rc.1', 'v2.10.0\nINJECT=true']) {
    assert.throws(() => planRelease(releases, tag));
  }
});
test('an existing newer major publication prevents a rollback even with stale API data', () => {
  const previous = { schema: 1, channels: { '2.x': 'v2.11.0' }, latest: 'v2.11.0' };
  assert.equal(planRelease(releases, 'v2.10.0', previous).publish, false);
});
test('Latest is protected independently from the major channel', () => {
  const previous = { schema: 1, channels: { '3.x': 'v3.0.0' }, latest: 'v3.0.0' };
  const plan = planRelease(releases, 'v2.10.0', previous);
  assert.equal(plan.publish, true);
  assert.equal(plan.latest, false);
  assert.equal(plan.manifest.channels['3.x'], 'v3.0.0');
  assert.equal(plan.manifest.latest, 'v3.0.0');
});
test('repeating the same release is idempotent', () => {
  const first = planRelease(releases, 'v2.10.0');
  assert.deepEqual(planRelease(releases, 'v2.10.0', first.manifest).manifest, first.manifest);
});
test('invalid publication manifests fail closed', () => {
  for (const previous of [{ schema: 9 }, { schema: 1, channels: { '2.x': 'v1.0.0' }, latest: null }]) {
    assert.throws(() => planRelease(releases, 'v2.10.0', previous), /manifest/);
  }
});
test('deployment is restricted to the package directory', () => {
  assert.equal(validateRemotePath('/var/www/docs/filament-demo/', 'filament-demo'), '/var/www/docs/filament-demo');
  for (const path of ['/', '/var/www/docs', '/var/www/../filament-demo', '/var/www/filament-demo;touch x']) {
    assert.throws(() => validateRemotePath(path, 'filament-demo'), /remote path/);
  }
});
test('all exact-commit required workflows must have succeeded', () => {
  const sha = 'a'.repeat(40);
  const runs = ['tests', 'quality', 'standard'].map((name, index) => ({
    id: index + 1, head_sha: sha, head_branch: '2.x', event: 'push',
    path: `.github/workflows/${name}.yml`, status: 'completed', conclusion: 'success',
  }));
  assert.doesNotThrow(() => requireSuccessfulChecks(runs, sha, '2.x'));
  assert.throws(() => requireSuccessfulChecks(runs.slice(1), sha, '2.x'), /tests.yml/);
  assert.throws(() => requireSuccessfulChecks([...runs, { ...runs[0], id: 99, conclusion: 'failure' }], sha, '2.x'), /tests.yml/);
  assert.throws(() => requireSuccessfulChecks(runs, 'b'.repeat(40), '2.x'), /tests.yml/);
});
