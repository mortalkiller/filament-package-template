import test from 'node:test';
import assert from 'node:assert/strict';
import { mkdtempSync, mkdirSync, writeFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { checkMajorFlow } from '../check-major-flow.mjs';

function fixture(overrides = {}) {
  const root = mkdtempSync(join(tmpdir(), 'package-flow-'));
  const files = {
    '.github/workflows/docs.yml': 'jobs:\n  docs:\n    uses: owner/repo/.github/workflows/docs.yml@' + 'a'.repeat(40) + '\n    with:\n      deploy: false\n',
    '.github/workflows/docs-release.yml': 'on:\n  release:\n    types: [published]\njobs:\n  docs:\n    if: github.event.release.prerelease == false\n',
    'docs-site/astro.config.mjs': "const base = process.env.DOCS_BASE_PATH; const edit = '/edit/1.x/';\n",
    ...overrides,
  };
  for (const [path, content] of Object.entries(files)) {
    mkdirSync(join(root, path, '..'), { recursive: true });
    writeFileSync(join(root, path), content);
  }
  return root;
}
for (const [name, overrides, expected] of [
  ['valid versioned workflows', {}, 0],
  ['mutable external references', { '.github/workflows/tests.yml': 'jobs:\n  tests:\n    uses: owner/repo/.github/workflows/tests.yml@1.x\n' }, 1],
  ['obsolete promotion-branch triggers', { '.github/workflows/tests.yml': 'on:\n  push:\n    branches:\n      - main\n' }, 1],
  ['publication from a push', { '.github/workflows/docs-release.yml': 'on:\n  push:\n' }, 1],
  ['deployment in validation', { '.github/workflows/docs.yml': 'jobs:\n  docs:\n    with:\n      deploy: true\n' }, 1],
  ['old documentation edit links', { 'docs-site/astro.config.mjs': "const base = process.env.DOCS_BASE_PATH; const edit = '/edit/main/';" }, 1],
]) {
  test(name, () => {
    const root = fixture(overrides);
    try { assert.equal(checkMajorFlow(root).length, expected); }
    finally { rmSync(root, { recursive: true, force: true }); }
  });
}
