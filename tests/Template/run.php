<?php

declare(strict_types=1);

$repo = dirname(__DIR__, 2);
$initializer = $repo.'/.template/initialize-package.php';

if (! is_file($initializer)) {
    fwrite(STDERR, "RED: initializer missing\n");
    exit(1);
}

$tmp = sys_get_temp_dir().'/fpt-'.bin2hex(random_bytes(4));
mkdir($tmp, 0777, true);

$copy = static function (string $source, string $target) use (&$copy): void {
    if (is_dir($source)) {
        @mkdir($target, 0777, true);
        foreach (scandir($source) ?: [] as $entry) {
            if (in_array($entry, ['.', '..', '.git', 'vendor', 'node_modules', '.superpowers'], true)) {
                continue;
            }
            $copy($source.'/'.$entry, $target.'/'.$entry);
        }

        return;
    }
    @mkdir(dirname($target), 0777, true);
    copy($source, $target);
};

$copy($repo, $tmp);

@mkdir($tmp.'/.github/workflows', 0777, true);
file_put_contents(
    $tmp.'/.github/workflows/tests.yml',
    "jobs:\n  tests:\n    uses: ./.github/workflows/reusable-tests.yml\n",
);
file_put_contents($tmp.'/.github/workflows/reusable-tests.yml', "name: reusable\n");

$cmd = sprintf(
    'PACKAGE_TEMPLATE_ROOT=%s php %s --slug=%s --title=%s --namespace=%s --description=%s 2>&1',
    escapeshellarg($tmp),
    escapeshellarg($initializer),
    escapeshellarg('filament-example'),
    escapeshellarg('Filament Example'),
    escapeshellarg('MortalKiller\\FilamentExample'),
    escapeshellarg('Example Filament package.'),
);
exec($cmd, $output, $code);
if ($code !== 0) {
    fwrite(STDERR, implode("\n", $output)."\n");
    exit(2);
}

$assertContains = static function (string $needle, string $path) use ($tmp): void {
    $full = $tmp.'/'.$path;
    $content = is_file($full) ? file_get_contents($full) : false;
    if (! is_string($content) || ! str_contains($content, $needle)) {
        fwrite(STDERR, "Missing [{$needle}] in [{$path}]\n");
        exit(3);
    }
};

$assertContains('"name": "mortalkiller/filament-example"', 'composer.json');
$assertContains('MortalKiller\\\\FilamentExample\\\\', 'composer.json');
$assertContains("basePath = '/filament-example'", 'docs-site/astro.config.mjs');
$assertContains("title: 'Filament Example'", 'docs-site/astro.config.mjs');
$assertContains("description: 'Example Filament package.'", 'docs-site/astro.config.mjs');
$assertContains('https://docs.pedromonteiro.dev/filament-example/', 'README.md');
$assertContains(
    'uses: mortalkiller/filament-package-template/.github/workflows/reusable-tests.yml@1.x',
    '.github/workflows/tests.yml',
);

if (! is_file($tmp.'/src/FilamentExampleServiceProvider.php')) {
    fwrite(STDERR, "Renamed service provider missing\n");
    exit(4);
}

$composer = json_decode((string) file_get_contents($tmp.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
if (in_array('package-template', $composer['keywords'] ?? [], true)) {
    fwrite(STDERR, "Template-only Composer keyword leaked into generated package\n");
    exit(41);
}

$security = (string) file_get_contents($tmp.'/SECURITY.md');
if (str_contains($security, 'This template does not itself publish a runtime package')) {
    fwrite(STDERR, "Template-specific security copy leaked into generated package\n");
    exit(42);
}

foreach (['.template', 'tools', 'skills', 'tests/Template', 'tests/standard-checker'] as $removed) {
    if (file_exists($tmp.'/'.$removed)) {
        fwrite(STDERR, "Template-only path still exists: {$removed}\n");
        exit(5);
    }
}

$second = [];
$secondCode = 0;
exec($cmd, $second, $secondCode);
if ($secondCode === 0 || ! str_contains(implode("\n", $second), 'Package template has already been initialized.')) {
    fwrite(STDERR, "Second initialization was not rejected safely\n");
    exit(6);
}

$partial = sys_get_temp_dir().'/fpt-partial-'.bin2hex(random_bytes(4));
mkdir($partial, 0777, true);
$copy($repo, $partial);

$partialComposer = (string) file_get_contents($partial.'/composer.json');
$partialComposer = str_replace(
    '"name": "mortalkiller/filament-package-template"',
    '"name": "mortalkiller/filament-example"',
    $partialComposer,
);
file_put_contents($partial.'/composer.json', $partialComposer);

$state = [
    'slug' => 'filament-example',
    'title' => 'Filament Example',
    'namespace' => 'MortalKiller\\FilamentExample',
    'description' => 'Example Filament package.',
];
file_put_contents(
    $partial.'/.template/.initializing.json',
    json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
);

$partialCmd = sprintf(
    'PACKAGE_TEMPLATE_ROOT=%s php %s --slug=%s --title=%s --namespace=%s --description=%s 2>&1',
    escapeshellarg($partial),
    escapeshellarg($initializer),
    escapeshellarg($state['slug']),
    escapeshellarg($state['title']),
    escapeshellarg($state['namespace']),
    escapeshellarg($state['description']),
);
exec($partialCmd, $partialOutput, $partialCode);
if ($partialCode !== 0 || file_exists($partial.'/.template')) {
    fwrite(STDERR, "Matching partial initialization did not resume safely:\n".implode("\n", $partialOutput)."\n");
    exit(7);
}

$mismatch = sys_get_temp_dir().'/fpt-mismatch-'.bin2hex(random_bytes(4));
mkdir($mismatch, 0777, true);
$copy($repo, $mismatch);
file_put_contents(
    $mismatch.'/.template/.initializing.json',
    json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n",
);
$mismatchCmd = sprintf(
    'PACKAGE_TEMPLATE_ROOT=%s php %s --slug=%s --title=%s --namespace=%s --description=%s 2>&1',
    escapeshellarg($mismatch),
    escapeshellarg($initializer),
    escapeshellarg('filament-different'),
    escapeshellarg($state['title']),
    escapeshellarg($state['namespace']),
    escapeshellarg($state['description']),
);
exec($mismatchCmd, $mismatchOutput, $mismatchCode);
if (
    $mismatchCode === 0
    || ! str_contains(implode("\n", $mismatchOutput), 'Initialization is already in progress with different arguments.')
) {
    fwrite(STDERR, "Mismatched partial initialization was not rejected safely\n");
    exit(8);
}

echo "initializer smoke test passed\n";
