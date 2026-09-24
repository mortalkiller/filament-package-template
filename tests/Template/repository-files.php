<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requirements = [
    '.github/ISSUE_TEMPLATE/bug_report.yml' => ['Package version', 'Filament version', 'Laravel version', 'PHP version'],
    '.github/ISSUE_TEMPLATE/feature_request.yml' => ['Problem', 'Proposed API', 'Non-goals', 'Acceptance criteria'],
    '.github/PULL_REQUEST_TEMPLATE.md' => ['Related issue', 'Verification', 'Documentation', 'Public-content check'],
    '.github/dependabot.yml' => ['composer', 'github-actions'],
];

foreach ($requirements as $path => $needles) {
    $full = $root.'/'.$path;
    if (! is_file($full)) {
        fwrite(STDERR, "Missing required repository file: {$path}\n");
        exit(1);
    }
    $content = (string) file_get_contents($full);
    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "Missing [{$needle}] in [{$path}]\n");
            exit(2);
        }
    }
}

$astro = $root.'/docs-site/astro.config.mjs';
if (! is_file($astro)) {
    fwrite(STDERR, "Missing required repository file: docs-site/astro.config.mjs\n");
    exit(3);
}
$astroContent = (string) file_get_contents($astro);
foreach ([
    "const basePath = '/filament-package-template'",
    "site: 'https://docs.pedromonteiro.dev'",
    'https://github.com/mortalkiller/filament-package-template',
    'https://pedromonteiro.dev',
] as $needle) {
    if (! str_contains($astroContent, $needle)) {
        fwrite(STDERR, "Missing [{$needle}] in [docs-site/astro.config.mjs]\n");
        exit(4);
    }
}

$release = $root.'/docs/releasing.md';
if (! is_file($release)) {
    fwrite(STDERR, "Missing required repository file: docs/releasing.md\n");
    exit(5);
}
$releaseContent = (string) file_get_contents($release);
foreach ([
    'PlumbPHP Ecosystem 100',
    'PlumbPHP Maintenance 100',
    'PlumbPHP Security 100',
    'PlumbPHP Composite 100',
    'vX.Y.Z',
] as $needle) {
    if (! str_contains($releaseContent, $needle)) {
        fwrite(STDERR, "Missing [{$needle}] in [docs/releasing.md]\n");
        exit(6);
    }
}

$docsReleaseWorkflow = $root.'/.github/workflows/docs-release.yml';
$reusableDocsReleaseWorkflow = $root.'/.github/workflows/reusable-docs-release.yml';
if (! is_file($docsReleaseWorkflow) || ! is_file($reusableDocsReleaseWorkflow)) {
    fwrite(STDERR, "Missing release documentation workflow files\n");
    exit(7);
}

$docsReleaseContent = (string) file_get_contents($docsReleaseWorkflow);
foreach ([
    'environment: docs-production',
    'DOCS_SSH_PRIVATE_KEY',
    'DOCS_SSH_KNOWN_HOSTS',
    'DOCS_HOST',
    'DOCS_USER',
    'package-manager-cache: false',
] as $needle) {
    if (! str_contains($docsReleaseContent, $needle)) {
        fwrite(STDERR, "Missing [{$needle}] in [.github/workflows/docs-release.yml]\n");
        exit(8);
    }
}

$reusableDocsReleaseContent = (string) file_get_contents($reusableDocsReleaseWorkflow);
if (
    str_contains($reusableDocsReleaseContent, 'secrets.DOCS_')
    || str_contains($reusableDocsReleaseContent, 'environment: docs-production')
    || str_contains($reusableDocsReleaseContent, 'environment: ${{ inputs.environment-name }}')
) {
    fwrite(STDERR, "Reusable release workflow must not own deployment secrets or environments\n");
    exit(9);
}

echo "repository files smoke test passed\n";
