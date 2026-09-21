import { readFileSync, writeFileSync, appendFileSync, existsSync, mkdirSync } from 'node:fs';
import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

const tagPattern = /^v(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/;
const requiredWorkflows = ['tests', 'quality', 'standard'];

function version(tag) {
  const match = typeof tag === 'string' && tag.length < 80 && tag.match(tagPattern);
  if (!match) throw new Error('Expected a stable vX.Y.Z tag.');
  return match.slice(1).map(BigInt);
}
function compare(left, right) {
  const a = version(left);
  const b = version(right);
  for (let index = 0; index < 3; index++) {
    if (a[index] !== b[index]) return a[index] > b[index] ? 1 : -1;
  }
  return 0;
}
function majorOf(tag) { return `${version(tag)[0]}.x`; }
function stable(release) {
  return release && release.draft === false && release.prerelease === false
    && typeof release.tag_name === 'string' && tagPattern.test(release.tag_name);
}
function readManifest(previous) {
  if (!previous || typeof previous !== 'object' || Array.isArray(previous)) throw new Error('Invalid documentation manifest.');
  if (Object.keys(previous).length === 0) return { schema: 1, latest: null, channels: {} };
  if (previous.schema !== 1 || !previous.channels || typeof previous.channels !== 'object' || Array.isArray(previous.channels)) {
    throw new Error('Invalid documentation manifest schema.');
  }
  try {
    for (const [channel, tag] of Object.entries(previous.channels)) {
      if (majorOf(tag) !== channel) throw new Error('Channel does not match its tag.');
    }
    if (previous.latest !== null && !Object.values(previous.channels).includes(previous.latest)) throw new Error('Latest is not a published channel.');
  } catch { throw new Error('Invalid documentation manifest versions.'); }
  return { schema: 1, latest: previous.latest, channels: { ...previous.channels } };
}

export function planRelease(releases, tag, previous = {}) {
  version(tag);
  if (!Array.isArray(releases)) throw new Error('Invalid releases response.');
  const published = releases.filter(stable).map((release) => release.tag_name);
  if (!published.includes(tag)) throw new Error('The tag must identify a published stable release.');
  const major = majorOf(tag);
  const manifest = readManifest(previous);
  const newest = [...published].sort(compare).at(-1);
  const newestMajor = published.filter((item) => majorOf(item) === major).sort(compare).at(-1);
  const current = manifest.channels[major];
  const publish = tag === newestMajor && (!current || compare(tag, current) >= 0);
  const latest = publish && tag === newest && (!manifest.latest || compare(tag, manifest.latest) >= 0);
  if (publish) manifest.channels[major] = tag;
  if (latest) manifest.latest = tag;
  return { tag, major, publish, latest, manifest };
}

export function validateRemotePath(path, slug) {
  if (!/^filament-[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) throw new Error('Invalid package slug.');
  const normalized = String(path).replace(/\/+$/, '');
  if (!/^\/(?:[A-Za-z0-9_.-]+\/)*[A-Za-z0-9_.-]+$/.test(normalized)
      || normalized.split('/').some((part) => part === '.' || part === '..')
      || !normalized.endsWith(`/${slug}`)) {
    throw new Error('The remote path must be an absolute package directory ending in the package slug.');
  }
  return normalized;
}

export function requireSuccessfulChecks(runs, sha, major) {
  if (!/^[a-f0-9]{40}$/.test(sha)) throw new Error('Invalid release commit SHA.');
  for (const name of requiredWorkflows) {
    const path = `.github/workflows/${name}.yml`;
    const candidates = runs.filter((run) => run.head_sha === sha && run.head_branch === major
      && run.event === 'push' && run.path === path).sort((a, b) => b.id - a.id);
    if (candidates[0]?.status !== 'completed' || candidates[0]?.conclusion !== 'success') {
      throw new Error(`The release commit needs a successful ${path} push run on ${major}. Wait for CI and rerun publication.`);
    }
  }
}

async function api(path) {
  const response = await fetch(`https://api.github.com${path}`, {
    headers: { Accept: 'application/vnd.github+json', Authorization: `Bearer ${process.env.GITHUB_TOKEN}`, 'X-GitHub-Api-Version': '2022-11-28' },
    signal: AbortSignal.timeout(30000),
  });
  if (!response.ok) throw new Error(`GitHub API request failed with HTTP ${response.status}.`);
  return response.json();
}
async function pages(path, field = null) {
  const result = [];
  for (let page = 1; page <= 100; page++) {
    const data = await api(`${path}${path.includes('?') ? '&' : '?'}per_page=100&page=${page}`);
    const items = field ? data[field] : data;
    if (!Array.isArray(items)) throw new Error('Unexpected GitHub pagination response.');
    result.push(...items);
    if (items.length < 100) return result;
  }
  throw new Error('GitHub pagination limit exceeded; refusing an incomplete version list.');
}
function command(name, args, options = {}) {
  return execFileSync(name, args, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'], ...options }).trim();
}
function identity() {
  const repository = process.env.GITHUB_REPOSITORY ?? '';
  if (!/^[A-Za-z0-9_.-]+\/filament-[a-z0-9]+(?:-[a-z0-9]+)*$/.test(repository)) throw new Error('Invalid repository identity.');
  return { repository, slug: repository.split('/')[1] };
}
function output(values) {
  for (const [key, value] of Object.entries(values)) {
    if (process.env.GITHUB_OUTPUT) appendFileSync(process.env.GITHUB_OUTPUT, `${key}=${value}\n`);
  }
}

async function prepare() {
  const { repository, slug } = identity();
  const tag = process.env.RELEASE_TAG;
  const releases = await pages(`/repos/${repository}/releases`);
  const plan = planRelease(releases, tag);
  output({ publish: plan.publish, tag, major: plan.major, slug });
  if (!plan.publish) { console.log('An updated release exists for this major; no publication is needed.'); return; }
  const sha = command('git', ['rev-parse', 'HEAD']);
  command('git', ['fetch', '--no-tags', 'origin', `+refs/heads/${plan.major}:refs/remotes/origin/${plan.major}`]);
  command('git', ['merge-base', '--is-ancestor', sha, `refs/remotes/origin/${plan.major}`]);
  requireSuccessfulChecks(await pages(`/repos/${repository}/actions/runs?head_sha=${sha}`, 'workflow_runs'), sha, plan.major);
  const directory = process.env.DOCS_DIRECTORY || 'docs-site';
  if (!/^[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*$/.test(directory)) throw new Error('Invalid documentation directory.');
  if (!readFileSync(`${directory}/astro.config.mjs`, 'utf8').includes('DOCS_BASE_PATH')) {
    throw new Error('This tag predates versioned documentation. Publish a new release containing the migration; do not move an existing tag.');
  }
  mkdirSync('.docs-release', { recursive: true });
  writeFileSync('.docs-release/plan.json', JSON.stringify({ ...plan, sha, repository }, null, 2));
  console.log(`Verified ${tag} on ${plan.major} at ${sha}.`);
}

function prepareAssets() {
  const { slug } = identity();
  const directory = process.env.DOCS_DIRECTORY || 'docs-site';
  const base = process.env.DOCS_BASE_PATH;
  if (!new RegExp(`^/${slug}(?:/(0|[1-9]\\d*)\\.x)?$`).test(base ?? '')) throw new Error('Invalid documentation base path.');
  for (const filename of ['site.webmanifest', 'browserconfig.xml']) {
    const path = `${directory}/public/${filename}`;
    if (!existsSync(path)) continue;
    const original = command('git', ['show', `HEAD:${path}`]);
    writeFileSync(path, original.replaceAll(`/${slug}/`, `${base}/`) + '\n');
  }
}

async function publish() {
  const { repository, slug } = identity();
  const saved = JSON.parse(readFileSync('.docs-release/plan.json', 'utf8'));
  if (saved.repository !== repository || saved.tag !== process.env.RELEASE_TAG) throw new Error('Publication artifact does not match this release.');
  const root = validateRemotePath(process.env.DOCS_REMOTE_PATH, slug);
  const host = process.env.DOCS_HOST ?? '';
  const user = process.env.DOCS_USER ?? '';
  const port = process.env.DOCS_PORT || '22';
  if (!/^[a-zA-Z0-9][a-zA-Z0-9.-]*$/.test(host) || !/^[a-zA-Z_][a-zA-Z0-9_-]*$/.test(user)
      || !/^\d{1,5}$/.test(port) || Number(port) < 1 || Number(port) > 65535) throw new Error('Invalid SSH destination configuration.');
  const target = `${user}@${host}`;
  const key = `${process.env.HOME}/.ssh/id_ed25519`;
  const sshArgs = ['-o', 'BatchMode=yes', '-o', 'StrictHostKeyChecking=yes', '-p', port, '-i', key];
  const remote = (script) => command('ssh', [...sshArgs, target, script]);
  const previousText = remote(`if test -f '${root}/versions.json'; then cat -- '${root}/versions.json'; else printf '{}'; fi`);
  const previous = JSON.parse(previousText);
  const plan = planRelease(await pages(`/repos/${repository}/releases`), saved.tag, previous);
  if (!plan.publish) { console.log('A newer publication exists; refusing to downgrade documentation.'); return; }
  const transport = `ssh -o BatchMode=yes -o StrictHostKeyChecking=yes -p ${port} -i ${key}`;
  remote(`mkdir -p -- '${root}/${plan.major}'`);
  command('rsync', ['-az', '--delete', '-e', transport, '.docs-release/major/', `${target}:${root}/${plan.major}/`]);
  if (plan.latest) {
    // Root remains the canonical Latest URL; numeric major directories are protected from deletion.
    command('rsync', ['-az', '--delete', '--exclude=[0-9]*.x/', '--exclude=versions.json', '-e', transport, '.docs-release/latest/', `${target}:${root}/`]);
  }
  writeFileSync('.docs-release/versions.json', JSON.stringify(plan.manifest, null, 2) + '\n');
  command('rsync', ['-az', '-e', transport, '.docs-release/versions.json', `${target}:${root}/versions.json.tmp`]);
  remote(`mv -- '${root}/versions.json.tmp' '${root}/versions.json'`);
  console.log(`Published ${plan.tag} to ${plan.major}${plan.latest ? ' and Latest' : ''}. Other major channels were preserved.`);
}

if (process.argv[1] && import.meta.url === pathToFileURL(resolve(process.argv[1])).href) {
  try {
    if (process.argv[2] === 'prepare') await prepare();
    else if (process.argv[2] === 'assets') prepareAssets();
    else if (process.argv[2] === 'publish') await publish();
    else throw new Error('Expected prepare, assets, or publish.');
  } catch (error) {
    // Do not print SSH command arguments, credentials, or private deployment paths.
    console.error(error?.status !== undefined ? 'An external verification or deployment command failed. No success is claimed.' : error.message);
    process.exitCode = 1;
  }
}
