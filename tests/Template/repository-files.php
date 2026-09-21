<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$requirements = [
    'resources/boost/skills/developing-filament-packages/SKILL.md' => ['name: developing-filament-packages', 'description:', 'Laravel Boost'],
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

echo "repository files smoke test passed\n";
