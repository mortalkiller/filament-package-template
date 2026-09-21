import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

export function checkMajorFlow(root) {
  const directory = `${root}/.github/workflows`;
  const errors = [];
  if (!existsSync(`${directory}/docs-release.yml`)) return errors;
  for (const name of readdirSync(directory).filter((file) => /\.ya?ml$/.test(file))) {
    const content = readFileSync(`${directory}/${name}`, 'utf8');
    for (const match of content.matchAll(/^\s*(?:-\s*)?uses:\s*([^\s#]+).*$/gm)) {
      const reference = match[1];
      if (!reference.startsWith('./') && !/@[a-f0-9]{40}$/.test(reference)) errors.push(`${name}: external uses must be pinned by a full commit SHA.`);
    }
    if (/refs\/heads\/main|^\s*-\s*['"]?main['"]?\s*$/m.test(content)) errors.push(`${name}: the major-only flow must not depend on the old promotion branch.`);
  }
  const validation = readFileSync(`${directory}/docs.yml`, 'utf8');
  if (!/^\s+deploy:\s*false\s*$/m.test(validation) || /^\s+secrets:\s*inherit\s*$/m.test(validation)) {
    errors.push('docs.yml: validation must disable deployment and must not inherit deployment secrets.');
  }
  const publication = readFileSync(`${directory}/docs-release.yml`, 'utf8');
  if (/^\s{2}(push|pull_request):/m.test(publication) || !/types:\s*\[published\]/.test(publication)
      || !publication.includes('github.event.release.prerelease == false')) {
    errors.push('docs-release.yml: publication must be stable-release-only, with explicit prerelease filtering.');
  }
  const config = readFileSync(`${root}/docs-site/astro.config.mjs`, 'utf8');
  if (!config.includes('DOCS_BASE_PATH') || config.includes('/edit/main/')) errors.push('astro.config.mjs: versioned paths and major-aware edit links are required.');
  return errors;
}

if (process.argv[1] && import.meta.url === pathToFileURL(resolve(process.argv[1])).href) {
  const errors = checkMajorFlow(resolve(process.argv[2] || '.'));
  for (const error of errors) console.error(error);
  if (!errors.length) console.log('Major-only workflow checks passed.');
  process.exitCode = errors.length ? 1 : 0;
}
