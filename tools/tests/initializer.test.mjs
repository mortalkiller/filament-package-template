import test from 'node:test';
import assert from 'node:assert/strict';
import { mkdtempSync, mkdirSync, writeFileSync, readFileSync, existsSync, rmSync, cpSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { resolve, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const source = resolve(fileURLToPath(new URL('../../', import.meta.url)));
const sha = 'a'.repeat(40);
const state = { slug: 'filament-example', title: 'Filament Example', namespace: 'MortalKiller\\FilamentExample', description: 'Example Filament package.', workflow_ref: sha };
function fixture(partial = false) {
  const root = mkdtempSync(join(tmpdir(), 'major-init-'));
  const put = (name, content) => { mkdirSync(resolve(root, name, '..'), { recursive: true }); writeFileSync(join(root, name), content); };
  put('.template/initialize-package.php', readFileSync(join(source, '.template/initialize-package.php')));
  put('.template/README.package.md', '# Filament Package Template\n');
  put('composer.json', JSON.stringify({ name: 'mortalkiller/filament-package-template' }));
  put('src/FilamentPackageTemplateServiceProvider.php', '<?php namespace MortalKiller\\FilamentPackageTemplate;');
  cpSync(join(source, '.github/workflows'), join(root, '.github/workflows'), { recursive: true });
  if (partial) {
    put('.template/.initializing.json', JSON.stringify(state));
    put('.github/workflows/tests.yml', `jobs:\n  tests:\n    uses: mortalkiller/filament-package-template/.github/workflows/reusable-tests.yml@${sha}\n`);
  }
  return root;
}
function initialize(root, ref = sha) {
  const args = Object.entries(state).filter(([key]) => key !== 'workflow_ref').map(([key, value]) => `--${key}=${value}`);
  return execFileSync('php', [join(root, '.template/initialize-package.php'), ...args, `--workflow-ref=${ref}`], { env: { ...process.env, PACKAGE_TEMPLATE_ROOT: root }, encoding: 'utf8', stdio: 'pipe' });
}
test('new packages pin both external workflows and tooling to the requested SHA', () => {
  const root = fixture();
  try {
    initialize(root);
    for (const name of ['standard', 'docs-release']) {
      const content = readFileSync(join(root, `.github/workflows/${name}.yml`), 'utf8');
      assert.ok(content.includes(`@${sha}`));
      assert.ok(content.includes(`standard-ref: ${sha}`));
      assert.ok(!content.includes('${{ github.sha }}'));
    }
    assert.ok(!existsSync(join(root, '.github/workflows/reusable-docs-release.yml')));
    assert.ok(readFileSync(join(root, 'AGENTS.md'), 'utf8').includes('mortalkiller/filament-package-standard/blob/1.x/docs/package-standard.md'));
    const composer = JSON.parse(readFileSync(join(root, 'composer.json'), 'utf8'));
    assert.equal(composer['require-dev']['mortalkiller/filament-package-standard'], '^1.0');
  } finally { rmSync(root, { recursive: true, force: true }); }
});
test('invalid workflow references are rejected before persistent changes', () => {
  const root = fixture();
  try {
    assert.throws(() => initialize(root, '1.x'));
    assert.ok(!existsSync(join(root, '.template/.initializing.json')));
  } finally { rmSync(root, { recursive: true, force: true }); }
});
test('resuming initialization does not rewrite canonical workflow ownership', () => {
  const root = fixture(true);
  try {
    initialize(root);
    const content = readFileSync(join(root, '.github/workflows/tests.yml'), 'utf8');
    assert.ok(content.includes(`mortalkiller/filament-package-template/.github/workflows/reusable-tests.yml@${sha}`));
  } finally { rmSync(root, { recursive: true, force: true }); }
});
