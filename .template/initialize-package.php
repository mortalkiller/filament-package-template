<?php

declare(strict_types=1);

const TEMPLATE_SLUG = 'filament-package-template';
const TEMPLATE_TITLE = 'Filament Package Template';
const TEMPLATE_NAMESPACE = 'MortalKiller\\FilamentPackageTemplate';
const TEMPLATE_STEM = 'FilamentPackageTemplate';
const TEMPLATE_DESCRIPTION = 'Template for MortalKiller Filament packages.';
const TEMPLATE_SNAKE = 'filament_package_template';

$root = getenv('PACKAGE_TEMPLATE_ROOT');
$root = is_string($root) && $root !== '' ? $root : dirname(__DIR__);
$root = rtrim($root, DIRECTORY_SEPARATOR);
$options = getopt('', [
    'slug:',
    'title:',
    'namespace:',
    'description:',
    'workflow-ref:',
    'type:',
    'with-config',
    'with-database',
    'with-views',
    'with-translations',
    'with-stubs',
    'with-assets',
    'with-workbench',
    'with-browser-tests',
    'with-rector',
]);

$slug = trim((string) ($options['slug'] ?? ''));
$title = trim((string) ($options['title'] ?? ''));
$namespace = trim((string) ($options['namespace'] ?? ''));
$description = trim((string) ($options['description'] ?? ''));
$workflowRef = trim((string) ($options['workflow-ref'] ?? ''));
$type = trim((string) ($options['type'] ?? 'plugin'));

$capabilities = [
    'config' => array_key_exists('with-config', $options),
    'database' => array_key_exists('with-database', $options),
    'views' => array_key_exists('with-views', $options),
    'translations' => array_key_exists('with-translations', $options),
    'stubs' => array_key_exists('with-stubs', $options),
    'assets' => array_key_exists('with-assets', $options),
    'workbench' => array_key_exists('with-workbench', $options),
    'browser-tests' => array_key_exists('with-browser-tests', $options),
    'rector' => array_key_exists('with-rector', $options),
];

if ($type === 'theme') {
    $capabilities['assets'] = true;
}
if ($capabilities['browser-tests']) {
    $capabilities['workbench'] = true;
}

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
$profiles = [
    'plugin' => 'filament/filament',
    'theme' => 'filament/filament',
    'forms' => 'filament/forms',
    'tables' => 'filament/tables',
    'library' => 'filament/support',
];
if (! array_key_exists($type, $profiles)) {
    $fail('The --type value must be one of: plugin, theme, forms, tables, library.');
}

$enabledCapabilities = array_keys(array_filter($capabilities));
sort($enabledCapabilities);
$requestedState = [
    'slug' => $slug,
    'title' => $title,
    'namespace' => $namespace,
    'description' => $description,
    'workflow_ref' => $workflowRef,
    'type' => $type,
    'capabilities' => $enabledCapabilities,
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
$packageSnake = str_replace('-', '_', $slug);
$runtimePackage = $profiles[$type];
$hasPanelPlugin = in_array($type, ['plugin', 'theme'], true);

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

$copyTree = static function (string $source, string $target) use (&$copyTree, $fail): void {
    if (! file_exists($source)) {
        $fail('Missing scaffold ['.$source.'].');
    }
    if (is_file($source)) {
        if (! is_dir(dirname($target)) && ! mkdir(dirname($target), 0777, true) && ! is_dir(dirname($target))) {
            $fail('Unable to create scaffold directory ['.dirname($target).'].');
        }
        if (! copy($source, $target)) {
            $fail('Unable to copy scaffold ['.$source.'].');
        }
        return;
    }
    if (! is_dir($target) && ! mkdir($target, 0777, true) && ! is_dir($target)) {
        $fail('Unable to create scaffold directory ['.$target.'].');
    }
    foreach (scandir($source) ?: [] as $entry) {
        if ($entry !== '.' && $entry !== '..') {
            $copyTree($source.'/'.$entry, $target.'/'.$entry);
        }
    }
};

$scaffolds = $templateDirectory.'/scaffolds';
if ($capabilities['config']) {
    $copyTree($scaffolds.'/config/config.php', $root.'/config/'.$slug.'.php');
}
if ($capabilities['database']) {
    $copyTree($scaffolds.'/database/create_table.php.stub', $root.'/database/migrations/create_'.$packageSnake.'_table.php.stub');
}
if ($capabilities['views']) {
    $copyTree($scaffolds.'/views', $root.'/resources/views');
}
if ($capabilities['translations']) {
    $copyTree($scaffolds.'/translations/en/package.php', $root.'/resources/lang/en/'.$slug.'.php');
}
if ($capabilities['stubs']) {
    $copyTree($scaffolds.'/stubs', $root.'/stubs');
}
if ($capabilities['assets']) {
    $copyTree($scaffolds.'/assets/bin', $root.'/bin');
    $copyTree($scaffolds.'/assets/resources', $root.'/resources');
}
if ($capabilities['workbench']) {
    $copyTree($scaffolds.'/workbench/testbench.yaml', $root.'/testbench.yaml');
    $copyTree($scaffolds.'/workbench/workbench', $root.'/workbench');
}
if ($capabilities['browser-tests']) {
    $copyTree($scaffolds.'/browser/playwright.config.mjs', $root.'/playwright.config.mjs');
    $copyTree($scaffolds.'/browser/tests', $root.'/tests/Browser');
}

$packageReadme = $templateDirectory.'/README.package.md';
if (! is_file($packageReadme)) {
    $fail('The package README template is missing.');
}
if (! copy($packageReadme, $root.'/README.md')) {
    $fail('Unable to create the package README.');
}

$pluginRegistration = $hasPanelPlugin
    ? "\n            ->plugin(\\{$namespace}\\{$stem}Plugin::make())"
    : '';
$replace = [
    TEMPLATE_SLUG => $slug,
    TEMPLATE_TITLE => $title,
    str_replace('\\', '\\\\', TEMPLATE_NAMESPACE) => str_replace('\\', '\\\\', $namespace),
    TEMPLATE_NAMESPACE => $namespace,
    TEMPLATE_STEM => $stem,
    TEMPLATE_DESCRIPTION => $description,
    TEMPLATE_SNAKE => $packageSnake,
    '/* __PACKAGE_PLUGIN_REGISTRATION__ */' => $pluginRegistration,
    'standard-ref: ${{ github.sha }}' => 'standard-ref: '.$workflowRef,
    'ref: ${{ github.sha }} # __TEMPLATE_TOOLING_REF__' => 'ref: '.$workflowRef,
    '"filament_package":"filament/filament"' => '"filament_package":"'.$runtimePackage.'"',
];
foreach (['tests', 'quality', 'docs', 'docs-release', 'browser-tests', 'standard-check'] as $workflow) {
    $name = 'reusable-'.$workflow.'.yml';
    $replace['$/.github/workflows/'.$name] = 'mortalkiller/filament-package-template/.github/workflows/'.$name.'@'.$workflowRef;
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

$oldProvider = $root.'/src/'.TEMPLATE_STEM.'ServiceProvider.php';
$newProvider = $root.'/src/'.$stem.'ServiceProvider.php';
if (is_file($oldProvider) && $oldProvider !== $newProvider && ! rename($oldProvider, $newProvider)) {
    $fail('Unable to rename the package service provider.');
}
$oldPlugin = $root.'/src/'.TEMPLATE_STEM.'Plugin.php';
$newPlugin = $root.'/src/'.$stem.'Plugin.php';
if ($hasPanelPlugin) {
    if (is_file($oldPlugin) && $oldPlugin !== $newPlugin && ! rename($oldPlugin, $newPlugin)) {
        $fail('Unable to rename the Filament plugin class.');
    }
} else {
    @unlink($oldPlugin);
    @unlink($root.'/tests/Unit/PluginTest.php');
}

try {
    $generatedComposer = json_decode((string) file_get_contents($composerPath), true, flags: JSON_THROW_ON_ERROR);
    $generatedComposer['require'] ??= [];
    $generatedComposer['require-dev'] ??= [];
    $generatedComposer['autoload-dev'] ??= [];
    $generatedComposer['autoload-dev']['psr-4'] ??= [];
    $generatedComposer['scripts'] ??= [];
    foreach (['filament/filament', 'filament/forms', 'filament/tables', 'filament/support'] as $filamentPackage) {
        unset($generatedComposer['require'][$filamentPackage]);
    }
    $generatedComposer['require'][$runtimePackage] = '^5.8.1';
    $generatedComposer['require-dev']['mortalkiller/filament-package-standard'] = '^2.0';

    if ($capabilities['workbench'] && $runtimePackage !== 'filament/filament') {
        $generatedComposer['require-dev']['filament/filament'] = '^5.8.1';
    }

    if ($capabilities['workbench']) {
        $generatedComposer['autoload-dev']['psr-4']['Workbench\\'] = 'workbench/';
        $generatedComposer['scripts']['serve'] = '@php vendor/bin/testbench serve';
    }

    if ($capabilities['browser-tests']) {
        $generatedComposer['scripts']['browser:prepare'] = '@php vendor/bin/testbench package:discover --ansi';
    }

    if (! $capabilities['rector']) {
        unset($generatedComposer['require-dev']['rector/rector']);
        unset($generatedComposer['scripts']['refactor'], $generatedComposer['scripts']['test:refactor']);
        $generatedComposer['scripts']['check'] = array_values(array_filter(
            $generatedComposer['scripts']['check'] ?? [],
            static fn (mixed $script): bool => $script !== '@test:refactor',
        ));
        @unlink($root.'/rector.php');
    }

    ksort($generatedComposer['require']);
    ksort($generatedComposer['require-dev']);
    ksort($generatedComposer['autoload-dev']['psr-4']);
    ksort($generatedComposer['scripts']);
    $generatedComposerContent = json_encode(
        $generatedComposer,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    ).PHP_EOL;
} catch (Throwable) {
    $fail('Unable to configure generated composer.json.');
}
if (file_put_contents($composerPath, $generatedComposerContent) === false) {
    $fail('Unable to write composer.json.');
}

if ($capabilities['assets'] || $capabilities['browser-tests']) {
    $packageJson = [
        'private' => true,
        'type' => 'module',
        'scripts' => [],
        'devDependencies' => [],
    ];
    if ($capabilities['assets']) {
        $packageJson['scripts']['dev'] = 'node bin/build.js --dev';
        $packageJson['scripts']['build'] = 'node bin/build.js';
        $packageJson['devDependencies']['esbuild'] = '^0.28.0';
        $packageJson['devDependencies']['prettier'] = '^3.5.3';
    }
    if ($capabilities['browser-tests']) {
        $packageJson['scripts']['test:browser'] = 'playwright test';
        $packageJson['devDependencies']['@playwright/test'] = '^1.55.0';
    }
    ksort($packageJson['scripts']);
    ksort($packageJson['devDependencies']);
    file_put_contents(
        $root.'/package.json',
        json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
    );

    $dependabotPath = $root.'/.github/dependabot.yml';
    $dependabot = (string) file_get_contents($dependabotPath);
    if (! str_contains($dependabot, "package-ecosystem: npm\n    directory: /\n")) {
        $dependabot .= <<<'YAML'

  - package-ecosystem: npm
    directory: /
    schedule:
      interval: weekly
    cooldown:
      default-days: 7
    open-pull-requests-limit: 5
YAML;
        $dependabot .= PHP_EOL;
        file_put_contents($dependabotPath, $dependabot);
    }
}

if ($capabilities['browser-tests']) {
    $browserWorkflow = <<<YAML
name: Browser tests

on:
  pull_request:
  push:
    branches: ['*.x']

permissions:
  contents: read

concurrency:
  group: browser-\${{ github.ref }}
  cancel-in-progress: true

jobs:
  browser:
    uses: mortalkiller/filament-package-template/.github/workflows/reusable-browser-tests.yml@{$workflowRef}
    with:
      filament-matrix-json: >-
        {"include":[{"filament":"^5.8.1"}]}
YAML;
    file_put_contents($root.'/.github/workflows/browser-tests.yml', $browserWorkflow.PHP_EOL);
}

$agentInstructions = <<<'MARKDOWN'
# Agent instructions

This repository follows MortalKiller Filament Package Standard v2.

Canonical standard: https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md
Installed maintainer skill: `vendor/mortalkiller/filament-package-standard/resources/boost/skills/developing-filament-packages/SKILL.md`
Canonical skill fallback: https://github.com/mortalkiller/filament-package-standard/blob/2.x/resources/boost/skills/developing-filament-packages/SKILL.md

After `composer install`, read and use the installed `developing-filament-packages` skill. If dependencies are not installed, use the canonical skill fallback. Read `docs/development-flow.md` and the canonical standard. Work on temporary branches targeting the affected major. Publish immutable tags on verified major commits; do not introduce a stable-promotion branch.

Before completion, run required CI-equivalent checks and audit public content for private customer, consumer, infrastructure and credential information. Distinguish code/build verification from an actual release or live documentation deployment.
MARKDOWN;
$contributing = <<<'MARKDOWN'
# Contributing

Read [Development and release flow](docs/development-flow.md) and the [canonical package standard](https://github.com/mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md).

After `composer install`, the maintainer skill is available at `vendor/mortalkiller/filament-package-standard/resources/boost/skills/developing-filament-packages/SKILL.md`.

Create a focused temporary branch from the affected major and open a PR to that same major. Include an issue and acceptance criteria for meaningful changes, preserve backwards compatibility, and update tests and source-derived documentation. Squash temporary branches after review and successful CI.

Run the repository's Composer validation, tests, Pint, tracked syntax, compatibility and documentation checks, plus static analysis and JavaScript/browser checks where configured. Do not publish customer data, private applications, infrastructure or secrets in examples.

Releases are immutable `vX.Y.Z` tags on verified commits in `X.x`. Stable GitHub Releases publish documentation from the tag; branch pushes do not. Use [SECURITY.md](SECURITY.md) for private vulnerability reporting.
MARKDOWN;
foreach (['AGENTS.md' => $agentInstructions, 'CONTRIBUTING.md' => $contributing] as $path => $content) {
    if (file_put_contents($root.'/'.$path, $content.PHP_EOL) === false) {
        $fail('Unable to write generated instructions.');
    }
}

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

fwrite(STDOUT, "Package initialized as mortalkiller/{$slug} ({$type}). Set the GitHub default branch to 1.x and run CI before publishing.".PHP_EOL);
