import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import {
  buildVersionOptions,
  clearVersionManifestCache,
  initializeVersionPickers,
  installVersionPicker,
  selectedVersionValue,
} from '../../docs-site/src/scripts/version-selector.js';

const component = readFileSync(new URL('../../docs-site/src/components/VersionedSiteTitle.astro', import.meta.url), 'utf8');
const css = readFileSync(new URL('../../docs-site/src/styles/custom.css', import.meta.url), 'utf8');
const root = '/filament-package-template';
const manifest = {
  schema: 1,
  latest: 'v12.3.4',
  channels: {
    '2.x': 'v2.8.0',
    '12.x': 'v12.3.4',
    '1.x': 'v1.0.1',
  },
};

test('keeps an accessible native select and delegates lifecycle behavior to the version selector module', () => {
  assert.match(component, /<select\b[^>]*class="docs-version-select"[^>]*aria-label="Documentation version"[^>]*data-docs-root=\{root\}[^>]*disabled/);
  assert.match(component, /class="docs-version-picker"/);
  assert.match(component, /class="docs-version-chevron"[\s\S]*?aria-hidden="true"[\s\S]*?focusable="false"/);
  assert.match(component, /import \{ installVersionPicker \} from '\.\.\/scripts\/version-selector\.js'/);
  assert.match(component, /void installVersionPicker\(\)/);
  assert.doesNotMatch(component, /<style\b/);
  assert.doesNotMatch(component, /astro-[a-z0-9]{6,}/);
});

test('theme presentation is scoped and covers native select states', () => {
  for (const selector of [
    '.docs-version-select',
    '.docs-version-select:focus-visible',
    '.docs-version-select:disabled',
    '.docs-version-select option',
    '.docs-version-chevron',
  ]) {
    assert.ok(css.includes(selector), `Missing scoped selector: ${selector}`);
  }

  assert.match(css, /appearance:\s*none/);
  assert.match(css, /background-color:\s*transparent/);
  assert.match(css, /var\(--sl-color-gray-2\)/);
  assert.match(css, /color-scheme:\s*dark/);
  assert.match(css, /color-scheme:\s*light/);
  assert.match(css, /pointer-events:\s*none/);
  assert.doesNotMatch(css, /(?:^|\})\s*(?:select|option)\s*\{/m);
});

test('renders one option per deployed major and marks Latest without duplicating the release', () => {
  assert.deepEqual(buildVersionOptions(manifest, root), [
    {
      major: '12.x',
      tag: 'v12.3.4',
      isLatest: true,
      label: '12.x (v12.3.4) — Latest',
      value: `${root}/`,
    },
    {
      major: '2.x',
      tag: 'v2.8.0',
      isLatest: false,
      label: '2.x (v2.8.0)',
      value: `${root}/2.x/`,
    },
    {
      major: '1.x',
      tag: 'v1.0.1',
      isLatest: false,
      label: '1.x (v1.0.1)',
      value: `${root}/1.x/`,
    },
  ]);
});

test('canonical Latest and the latest major channel select the same option', () => {
  const options = buildVersionOptions(manifest, root);

  assert.equal(selectedVersionValue(options, `${root}/guides/configuration/`, root), `${root}/`);
  assert.equal(selectedVersionValue(options, `${root}/12.x/guides/configuration/`, root), `${root}/`);
  assert.equal(selectedVersionValue(options, `${root}/2.x/guides/configuration/`, root), `${root}/2.x/`);
});

test('malformed manifests and channels do not create destinations', () => {
  assert.deepEqual(buildVersionOptions(null, root), []);
  assert.deepEqual(buildVersionOptions({ schema: 9, channels: {} }, root), []);
  assert.deepEqual(buildVersionOptions({ schema: 1 }, root), []);
  assert.deepEqual(
    buildVersionOptions({
      schema: 1,
      latest: 'v1.0.1',
      channels: {
        '1.x': 'v1.0.1',
        main: 'v1.0.1',
        '../2.x': 'v2.0.0',
        '2.x': 'v2.0.0-rc.1',
      },
    }, root),
    [{
      major: '1.x',
      tag: 'v1.0.1',
      isLatest: true,
      label: '1.x (v1.0.1) — Latest',
      value: `${root}/`,
    }],
  );
});

class FakeDocument {
  constructor() {
    this.documentElement = { dataset: {} };
    this.listeners = new Map();
    this.selects = [];
  }

  querySelectorAll(selector) {
    assert.equal(selector, 'select[data-docs-root]');

    return this.selects;
  }

  createElement(tagName) {
    assert.equal(tagName, 'option');

    return { dataset: {}, textContent: '', value: '' };
  }

  addEventListener(name, listener) {
    const listeners = this.listeners.get(name) ?? [];
    listeners.push(listener);
    this.listeners.set(name, listeners);
  }
}

class FakeSelect {
  constructor(documentObj) {
    this.ownerDocument = documentObj;
    this.dataset = { docsRoot: root };
    this.disabled = true;
    this.options = [];
    this.value = '';
    this.listeners = new Map();
  }

  replaceChildren(...options) {
    this.options = options;
  }

  addEventListener(name, listener) {
    const listeners = this.listeners.get(name) ?? [];
    listeners.push(listener);
    this.listeners.set(name, listeners);
  }
}

test('SPA page loads hydrate replacement selectors without duplicate listeners or manifest requests', async () => {
  clearVersionManifestCache();

  const documentObj = new FakeDocument();
  const firstSelect = new FakeSelect(documentObj);
  documentObj.selects = [firstSelect];

  let fetchCount = 0;
  const fetchImpl = async (url, options) => {
    fetchCount++;

    assert.equal(url, `${root}/versions.json`);
    assert.equal(options.cache, 'no-cache');

    return {
      ok: true,
      json: async () => manifest,
    };
  };
  const locationObj = {
    pathname: `${root}/guides/configuration/`,
    assigned: null,
    assign(value) {
      this.assigned = value;
    },
  };

  await installVersionPicker({ documentObj, fetchImpl, locationObj });

  assert.equal(fetchCount, 1);
  assert.equal(firstSelect.disabled, false);
  assert.equal(firstSelect.options.length, 3);
  assert.equal(firstSelect.value, `${root}/`);
  assert.equal(firstSelect.listeners.get('change')?.length, 1);
  assert.equal(documentObj.listeners.get('astro:page-load')?.length, 1);

  firstSelect.value = `${root}/2.x/`;
  firstSelect.listeners.get('change')[0]();
  assert.equal(locationObj.assigned, `${root}/2.x/`);

  locationObj.pathname = `${root}/2.x/api/`;
  const pageLoad = documentObj.listeners.get('astro:page-load')[0];
  await pageLoad();

  assert.equal(firstSelect.value, `${root}/2.x/`);
  assert.equal(fetchCount, 1);
  assert.equal(firstSelect.listeners.get('change')?.length, 1);

  const replacementSelect = new FakeSelect(documentObj);
  documentObj.selects = [replacementSelect];
  locationObj.pathname = `${root}/12.x/api/`;

  await pageLoad();

  assert.equal(replacementSelect.disabled, false);
  assert.equal(replacementSelect.options.length, 3);
  assert.equal(replacementSelect.value, `${root}/`);
  assert.equal(fetchCount, 1);
  assert.equal(replacementSelect.listeners.get('change')?.length, 1);

  await installVersionPicker({ documentObj, fetchImpl, locationObj });

  assert.equal(documentObj.listeners.get('astro:page-load')?.length, 1);
  assert.equal(fetchCount, 1);
});

test('failed manifests leave the fallback selector disabled and can retry later', async () => {
  clearVersionManifestCache();

  const documentObj = new FakeDocument();
  const select = new FakeSelect(documentObj);
  documentObj.selects = [select];
  const locationObj = { pathname: `${root}/`, assign() {} };

  let calls = 0;
  const fetchImpl = async () => {
    calls++;

    return calls === 1
      ? { ok: false, json: async () => manifest }
      : { ok: true, json: async () => manifest };
  };

  await initializeVersionPickers({ documentObj, fetchImpl, locationObj });
  assert.equal(select.disabled, true);
  assert.equal(select.dataset.docsVersionLoading, 'false');

  await initializeVersionPickers({ documentObj, fetchImpl, locationObj });
  assert.equal(select.disabled, false);
  assert.equal(calls, 2);
});
