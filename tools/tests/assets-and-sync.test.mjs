import test from 'node:test';
import assert from 'node:assert/strict';
import { mkdtempSync, mkdirSync, writeFileSync, readFileSync, existsSync, rmSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { latestSyncArgs } from '../docs-release.mjs';

const tool = fileURLToPath(new URL('../docs-release.mjs', import.meta.url));
test('asset rewriting uses the tag source and does not duplicate a version prefix', () => {
  const root = mkdtempSync(join(tmpdir(), 'docs-assets-'));
  try {
    mkdirSync(join(root, 'docs-site/public'), { recursive: true });
    writeFileSync(join(root, 'docs-site/public/site.webmanifest'), '{"icons":[{"src":"/filament-demo/icon.png"}]}\n');
    writeFileSync(join(root, 'docs-site/public/browserconfig.xml'), '<tile src="/filament-demo/icon.png"/>\n');
    const git = (...args) => execFileSync('git', args, { cwd: root, stdio: 'pipe' });
    git('init', '-q');
    git('add', '.');
    git('-c', 'user.name=Fixture', '-c', 'user.email=fixture@example.com', 'commit', '-qm', 'fixture');
    const run = (base) => execFileSync(process.execPath, [tool, 'assets'], {
      cwd: root, stdio: 'pipe', env: { ...process.env, GITHUB_REPOSITORY: 'example/filament-demo', DOCS_BASE_PATH: base },
    });
    run('/filament-demo/2.x');
    run('/filament-demo/2.x');
    assert.match(readFileSync(join(root, 'docs-site/public/site.webmanifest'), 'utf8'), /\/filament-demo\/2\.x\/icon\.png/);
    assert.doesNotMatch(readFileSync(join(root, 'docs-site/public/site.webmanifest'), 'utf8'), /2\.x\/2\.x/);
    run('/filament-demo');
    assert.doesNotMatch(readFileSync(join(root, 'docs-site/public/browserconfig.xml'), 'utf8'), /2\.x/);
  } finally { rmSync(root, { recursive: true, force: true }); }
});

test('actual rsync replaces Latest while preserving published major directories and its manifest', () => {
  const root = mkdtempSync(join(tmpdir(), 'docs-sync-'));
  try {
    const source = join(root, 'source');
    const target = join(root, 'target');
    mkdirSync(source);
    mkdirSync(join(target, '1.x'), { recursive: true });
    mkdirSync(join(target, '12.x'));
    writeFileSync(join(source, 'index.html'), 'new latest');
    writeFileSync(join(target, 'index.html'), 'old latest');
    writeFileSync(join(target, 'obsolete.html'), 'stale');
    writeFileSync(join(target, '1.x/index.html'), 'major one');
    writeFileSync(join(target, '12.x/index.html'), 'major twelve');
    writeFileSync(join(target, 'versions.json'), '{"schema":1}');
    execFileSync('rsync', latestSyncArgs('ssh', `${source}/`, `${target}/`), { stdio: 'pipe' });
    assert.equal(readFileSync(join(target, 'index.html'), 'utf8'), 'new latest');
    assert.equal(readFileSync(join(target, '1.x/index.html'), 'utf8'), 'major one');
    assert.equal(readFileSync(join(target, '12.x/index.html'), 'utf8'), 'major twelve');
    assert.equal(readFileSync(join(target, 'versions.json'), 'utf8'), '{"schema":1}');
    assert.equal(existsSync(join(target, 'obsolete.html')), false);
  } finally { rmSync(root, { recursive: true, force: true }); }
});
