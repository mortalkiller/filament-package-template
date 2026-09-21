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

$options = getopt('', ['slug:', 'title:', 'namespace:', 'description:']);
$slug = trim((string) ($options['slug'] ?? ''));
$title = trim((string) ($options['title'] ?? ''));
$namespace = trim((string) ($options['namespace'] ?? ''));
$description = trim((string) ($options['description'] ?? ''));

$fail = static function (string $message): never {
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
};

$composerPath = $root.'/composer.json';
if (! is_file($composerPath)) {
    $fail('Package template has already been initialized.');
}

try {
    $composer = json_decode((string) file_get_contents($composerPath), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable) {
    $fail('Unable to read composer.json.');
}

if (($composer['name'] ?? null) !== 'mortalkiller/'.TEMPLATE_SLUG || ! is_dir($root.'/.template')) {
    $fail('Package template has already been initialized.');
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

$segments = explode('\\', $namespace);
$stem = (string) end($segments);

$packageReadme = $root.'/.template/README.package.md';
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
    './.github/workflows/reusable-tests.yml' => 'mortalkiller/filament-package-template/.github/workflows/reusable-tests.yml@1.x',
    './.github/workflows/reusable-quality.yml' => 'mortalkiller/filament-package-template/.github/workflows/reusable-quality.yml@1.x',
    './.github/workflows/reusable-docs.yml' => 'mortalkiller/filament-package-template/.github/workflows/reusable-docs.yml@1.x',
    './.github/workflows/reusable-browser-tests.yml' => 'mortalkiller/filament-package-template/.github/workflows/reusable-browser-tests.yml@1.x',
    './.github/workflows/reusable-standard-check.yml' => 'mortalkiller/filament-package-template/.github/workflows/reusable-standard-check.yml@1.x',
];

$skipDirectories = ['.git', 'vendor', 'node_modules', 'dist', '.superpowers'];
$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static function (SplFileInfo $file) use ($skipDirectories): bool {
            return ! ($file->isDir() && in_array($file->getFilename(), $skipDirectories, true));
        },
    ),
);

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

    $updated = str_replace(array_keys($replace), array_values($replace), $content);
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
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $removeTree($path.'/'.$entry);
    }

    if (! @rmdir($path)) {
        throw new RuntimeException('Unable to remove ['.$path.'].');
    }
};

$templateOnly = [
    'tools',
    'skills',
    'tests/Template',
    'tests/standard-checker',
    'docs/package-standard.md',
    'docs/verification.md',
    '.github/workflows/reusable-tests.yml',
    '.github/workflows/reusable-quality.yml',
    '.github/workflows/reusable-docs.yml',
    '.github/workflows/reusable-browser-tests.yml',
    '.github/workflows/reusable-standard-check.yml',
];

try {
    foreach ($templateOnly as $relative) {
        $removeTree($root.'/'.$relative);
    }
    $removeTree($root.'/.template');
} catch (Throwable $exception) {
    $fail($exception->getMessage());
}

fwrite(STDOUT, "Package initialized as mortalkiller/{$slug}.".PHP_EOL);
