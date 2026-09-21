<?php

declare(strict_types=1);

const TEMPLATE_SLUG = 'filament-package-template';
const TEMPLATE_TITLE = 'Filament Package Template';
const TEMPLATE_NAMESPACE = 'MortalKiller\\FilamentPackageTemplate';
const TEMPLATE_STEM = 'FilamentPackageTemplate';
const TEMPLATE_DESCRIPTION = 'Template for MortalKiller Filament packages.';

$root = getenv('PACKAGE_TEMPLATE_ROOT');
$root = is_string($root) && $root !== '' ? $root : dirname(__DIR__);
$root = rtrim($root, DIRECTORY_SEPARATOR);
$options = getopt('', ['slug:', 'title:', 'namespace:', 'description:', 'workflow-ref:']);
$slug = trim((string) ($options['slug'] ?? ''));
$title = trim((string) ($options['title'] ?? ''));
$namespace = trim((string) ($options['namespace'] ?? ''));
$description = trim((string) ($options['description'] ?? ''));
$workflowRef = trim((string) ($options['workflow-ref'] ?? ''));

$fail = static function (string $message): never {
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
};

$templateDirectory = $root.'/.template';
if (! is_dir($templateDirectory)) {
    $fail('Package template has already been initialized.');
}

$composerPath = $root.'/composer.json';
if (! is_file($composerPath)) {
    $fail('Unable to read composer.json.');
}
try {
    $composer = json_decode((string) file_get_contents($composerPath), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable) {
    $fail('Unable to read composer.json.');
}

if (! preg_match('/^filament-[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
    $fail('The --slug value must match ^filament-[a-z0-9-]+$.');
}
if ($title === '') {
    $fail('The --title value must not be empty.');
}
if (! preg_match('/^MortalKiller\\\\[A-Za-z][A-Za-z0-9]*(?:\\\\[A-Za-z][A-Za-z0-9]*)*$/', $namespace)) {
    $fail('The --namespace value must be a MortalKiller namespace.');
}
if ($description === '') {
    $fail('The --description value must not be empty.');
}
if (! preg_match('/^[a-f0-9]{40}$/', $workflowRef)) {
    $fail('The --workflow-ref value must be a validated full 40-character template commit SHA.');
}

$requestedState = [
    'slug' => $slug,
    'title' => $title,
    'namespace' => $namespace,
    'description' => $description,
    'workflow_ref' => $workflowRef,
];
$statePath = $templateDirectory.'/.initializing.json';
if (is_file($statePath)) {
    try {
        $state = json_decode((string) file_get_contents($statePath), true, flags: JSON_THROW_ON_ERROR);
    } catch (Throwable) {
        $fail('Unable to read the existing initialization state.');
    }
    if ($state !== $requestedState) {
        $fail('Initialization is already in progress with different arguments.');
    }
    if (! in_array($composer['name'] ?? null, ['mortalkiller/'.TEMPLATE_SLUG, 'mortalkiller/'.$slug], true)) {
        $fail('The partially initialized package identity does not match the saved initialization state.');
    }
} else {
    if (($composer['name'] ?? null) !== 'mortalkiller/'.TEMPLATE_SLUG) {
        $fail('Package template has already been initialized.');
    }
    try {
        $encodedState = json_encode($requestedState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
    } catch (Throwable) {
        $fail('Unable to encode the initialization state.');
    }
    $temporaryState = $statePath.'.tmp';
    if (file_put_contents($temporaryState, $encodedState) === false || ! rename($temporaryState, $statePath)) {
        @unlink($temporaryState);
        $fail('Unable to persist the initialization state.');
    }
}

$segments = explode('\\', $namespace);
$stem = (string) end($segments);
$packageReadme = $templateDirectory.'/README.package.md';
if (! is_file($packageReadme)) {
    $fail('The package README template is missing.');
}
if (! copy($packageReadme, $root.'/README.md')) {
    $fail('Unable to create the package README.');
}

$replace = [
    TEMPLATE_SLUG => $slug,
    TEMPLATE_TITLE => $title,
    str_replace('\\', '\\\\', TEMPLATE_NAMESPACE) => str_replace('\\', '\\\\', $namespace),
    TEMPLATE_NAMESPACE => $namespace,
    TEMPLATE_STEM => $stem,
    TEMPLATE_DESCRIPTION => $description,
    'standard-ref: ${{ github.sha }}' => 'standard-ref: '.$workflowRef,
];
foreach (['tests', 'quality', 'docs', 'docs-release', 'browser-tests', 'standard-check'] as $workflow) {
    $name = 'reusable-'.$workflow.'.yml';
    $replace['./.github/workflows/'.$name] = 'mortalkiller/filament-package-template/.github/workflows/'.$name.'@'.$workflowRef;
}

$skipDirectories = ['.git', 'vendor', 'node_modules', 'dist', '.superpowers'];
$iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    static function (SplFileInfo $file) use ($skipDirectories): bool {
        return ! ($file->isDir() && in_array($file->getFilename(), $skipDirectories, true));
    },
));

/** @var list<array{path:string,content:string}> $writes */
$writes = [];
foreach ($iterator as $file) {
    if (! $file instanceof SplFileInfo || ! $file->isFile()) {
        continue;
    }
    $path = $file->getPathname();
    $content = file_get_contents($path);
    if (! is_string($content) || str_contains($content, "\0")) {
        continue;
    }
    // Preserve canonical references already written before an interrupted initialization.
    $protected = str_replace(
        'mortalkiller/filament-package-template/.github/workflows/',
        '__CANONICAL_PACKAGE_WORKFLOWS__/',
        $content,
    );
    $updated = str_replace(array_keys($replace), array_values($replace), $protected);
    $updated = str_replace(
        '__CANONICAL_PACKAGE_WORKFLOWS__/',
        'mortalkiller/filament-package-template/.github/workflows/',
        $updated,
    );
    if ($updated !== $content) {
        $writes[] = ['path' => $path, 'content' => $updated];
    }
}
foreach ($writes as $write) {
    $temporary = $write['path'].'.template-tmp';
    if (file_put_contents($temporary, $write['content']) === false || ! rename($temporary, $write['path'])) {
        @unlink($temporary);
        $fail('Unable to rewrite ['.$write['path'].'].');
    }
}

$agentInstructions = <<<'MARKDOWN'
# Agent instructions

This repository follows MortalKiller Filament Package Standard v1.

Canonical standard: https://github.com/mortalkiller/filament-package-standard/blob/1.x/docs/package-standard.md

Read `docs/development-flow.md` and the canonical standard. When the `developing-filament-packages` skill is available in your configured agent, use it. Work on temporary branches targeting the affected major. Publish immutable tags on verified major commits; do not introduce a stable-promotion branch.

Before completion, run required CI-equivalent checks and audit public content for private customer, consumer, infrastructure and credential information. Distinguish code/build verification from an actual release or live documentation deployment.
MARKDOWN;
$contributing = <<<'MARKDOWN'
# Contributing

Read [Development and release flow](docs/development-flow.md) and the [canonical package standard](https://github.com/mortalkiller/filament-package-standard/blob/1.x/docs/package-standard.md).

Create a focused temporary branch from the affected major and open a PR to that same major. Include an issue and acceptance criteria for meaningful changes, preserve backwards compatibility, and update tests and source-derived documentation. Squash temporary branches after review and successful CI.

Run the repository's Composer validation, tests, Pint, tracked syntax, compatibility and documentation checks, plus static analysis and JavaScript/browser checks where configured. Do not publish customer data, private applications, infrastructure or secrets in examples.

Releases are immutable `vX.Y.Z` tags on verified commits in `X.x`. Stable GitHub Releases publish documentation from the tag; branch pushes do not. Use [SECURITY.md](SECURITY.md) for private vulnerability reporting.
MARKDOWN;
foreach (['AGENTS.md' => $agentInstructions, 'CONTRIBUTING.md' => $contributing] as $path => $content) {
    if (file_put_contents($root.'/'.$path, $content.PHP_EOL) === false) {
        $fail('Unable to write generated instructions.');
    }
}

$oldProvider = $root.'/src/'.TEMPLATE_STEM.'ServiceProvider.php';
$newProvider = $root.'/src/'.$stem.'ServiceProvider.php';
if (is_file($oldProvider) && $oldProvider !== $newProvider && ! rename($oldProvider, $newProvider)) {
    $fail('Unable to rename the package service provider.');
}

$removeTree = static function (string $path) use (&$removeTree): void {
    if (! file_exists($path)) {
        return;
    }
    if (is_file($path) || is_link($path)) {
        if (! @unlink($path)) {
            throw new RuntimeException('Unable to remove ['.$path.'].');
        }
        return;
    }
    foreach (scandir($path) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            $removeTree($path.'/'.$entry);
        }
    }
    if (! @rmdir($path)) {
        throw new RuntimeException('Unable to remove ['.$path.'].');
    }
};

$templateOnly = ['tools', 'tests/Template', 'tests/standard-checker', 'docs/package-standard.md', 'docs/verification.md'];
foreach (['tests', 'quality', 'docs', 'docs-release', 'browser-tests', 'standard-check'] as $workflow) {
    $templateOnly[] = '.github/workflows/reusable-'.$workflow.'.yml';
}
try {
    foreach ($templateOnly as $relative) {
        $removeTree($root.'/'.$relative);
    }
    $removeTree($templateDirectory);
} catch (Throwable $exception) {
    $fail($exception->getMessage());
}

fwrite(STDOUT, "Package initialized as mortalkiller/{$slug}. Set the GitHub default branch to 1.x and run CI before publishing.".PHP_EOL);
