import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const component = readFileSync(new URL('../../docs-site/src/components/VersionedSiteTitle.astro', import.meta.url), 'utf8');
const css = readFileSync(new URL('../../docs-site/src/styles/custom.css', import.meta.url), 'utf8');
const root = '/filament-package-template';
const manifest = { schema: 1, latest: 'v12.3.4', channels: { '2.x': 'v2.8.0', '12.x': 'v12.3.4', '1.x': 'v1.0.1' } };

// Execute the component's unchanged browser script, replacing only its DOM-only type annotations.
const script = component.match(/<script>([\s\S]*?)<\/script>/)?.[1]
  ?.replace('querySelectorAll<HTMLSelectElement>', 'querySelectorAll')
  .replace(': HTMLOptionElement[]', '');
assert.ok(script, 'The version selector browser script must exist.');

async function runSelector(data, pathname = `${root}/`, { ok = true, reject = false } = {}) {
  const listeners = new Map();
  const select = {
    dataset: { docsRoot: root }, disabled: true, value: '', options: [],
    replaceChildren(...options) { this.options = options; },
    addEventListener(name, callback) { listeners.set(name, callback); },
  };
  const requests = [];
  const destinations = [];
  runInNewContext(script, {
    document: { querySelectorAll: () => [select] },
    Option: class { constructor(text, value) { this.text = text; this.value = value; } },
    location: { pathname, assign: (destination) => destinations.push(destination) },
    fetch: async (url, options) => {
      requests.push({ url, cache: options.cache });
      if (reject) throw new Error('Network unavailable.');
      return { ok, json: async () => data };
    },
  });
  await new Promise((resolve) => setImmediate(resolve));
  return { select, listeners, requests, destinations };
}

test('keeps an accessible native select and an inert theme-style chevron', () => {
  assert.match(component, /<select\b[^>]*class="docs-version-select"[^>]*aria-label="Documentation version"[^>]*data-docs-root=\{root\}[^>]*disabled/);
  assert.match(component, /class="docs-version-picker"/);
  assert.match(component, /class="docs-version-chevron"[\s\S]*?aria-hidden="true"[\s\S]*?focusable="false"/);
  assert.doesNotMatch(component, /<style\b/);
  assert.doesNotMatch(component, /astro-[a-z0-9]{6,}/);
});

test('theme presentation is scoped and covers native select states', () => {
  for (const selector of ['.docs-version-select', '.docs-version-select:focus-visible', '.docs-version-select:disabled', '.docs-version-select option', '.docs-version-chevron']) {
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

test('loads deployed channels, sorts numerically and selects Latest at the canonical URL', async () => {
  const result = await runSelector(manifest);
  assert.equal(result.select.disabled, false);
  assert.equal(result.select.value, `${root}/`);
  assert.deepEqual(Array.from(result.select.options, (option) => option.text), ['Latest (v12.3.4)', '12.x (v12.3.4)', '2.x (v2.8.0)', '1.x (v1.0.1)']);
  assert.deepEqual(result.requests, [{ url: `${root}/versions.json`, cache: 'no-cache' }]);
});

test('selects the active major even on a nested documentation page', async () => {
  const { select } = await runSelector(manifest, `${root}/2.x/guides/configuration/`);
  assert.equal(select.value, `${root}/2.x/`);
});

test('changing versions still navigates to the selected channel homepage', async () => {
  const result = await runSelector(manifest, `${root}/2.x/guides/configuration/`);
  result.select.value = `${root}/1.x/`;
  result.listeners.get('change')();
  result.select.value = `${root}/`;
  result.listeners.get('change')();
  assert.deepEqual(result.destinations, [`${root}/1.x/`, `${root}/`]);
});

test('missing, failed, malformed and empty manifests leave the control disabled', async () => {
  for (const [data, options] of [
    [manifest, { ok: false }], [manifest, { reject: true }], [null, {}],
    [{ schema: 9, channels: {} }, {}], [{ schema: 1 }, {}],
    [{ schema: 1, channels: {} }, {}],
  ]) {
    const result = await runSelector(data, `${root}/`, options);
    assert.equal(result.select.disabled, true);
    assert.equal(result.listeners.size, 0);
    assert.equal(result.destinations.length, 0);
  }
});

test('unpublished and malformed branch names cannot become destinations', async () => {
  const { select } = await runSelector({ schema: 1, channels: { '1.x': 'v1.0.1', main: 'v1.0.1', '../2.x': 'v2.0.0', '2.x': 'v2.0.0-rc.1' } });
  assert.deepEqual(Array.from(select.options, (option) => option.value), [`${root}/1.x/`]);
});
