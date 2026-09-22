<?php

declare(strict_types=1);

$repo = dirname(__DIR__, 2);
$initializer = $repo.'/.template/initialize-package.php';
$workflowRef = str_repeat('a', 40);
$base = [
    'slug' => 'filament-example',
    'title' => 'Filament Example',
    'namespace' => 'MortalKiller\\FilamentExample',
    'description' => 'Example Filament package.',
    'workflow_ref' => $workflowRef,
];
$state = $base + [
    'type' => 'plugin',
    'capabilities' => [],
];
$fail = static function (string $message): never {
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
};
if (! is_file($initializer)) {
    $fail('Initializer is missing.');
}
$copy = static function (string $source, string $target) use (&$copy): void {
    if (is_dir($source)) {
        @mkdir($target, 0777, true);
        foreach (scandir($source) ?: [] as $entry) {
            if (! in_array($entry, ['.', '..', '.git', 'vendor', 'node_modules', '.superpowers'], true)) {
                $copy($source.'/'.$entry, $target.'/'.$entry);
            }
        }

        return;
    }
    @mkdir(dirname($target), 0777, true);
    copy($source, $target);
};
$fixture = static function () use ($repo, $copy): string {
    $path = sys_get_temp_dir().'/fpt-'.bin2hex(random_bytes(4));
    $copy($repo, $path);

    return $path;
};
$run = static function (string $root, array $input) use ($initializer): array {
    $args = [];
    foreach ($input as $key => $value) {
        $name = '--'.str_replace('_', '-', $key);
        $args[] = $value === true ? $name : $name.'='.escapeshellarg((string) $value);
    }

    $previousRoot = getenv('PACKAGE_TEMPLATE_ROOT');
    putenv('PACKAGE_TEMPLATE_ROOT='.$root);
    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($initializer).' '.implode(' ', $args).' 2>&1';
    exec($command, $output, $code);

    if ($previousRoot === false) {
        putenv('PACKAGE_TEMPLATE_ROOT');
    } else {
        putenv('PACKAGE_TEMPLATE_ROOT='.$previousRoot);
    }

    return [$code, implode("\n", $output)];
};
$assertContains = static function (string $root, string $needle, string $path) use ($fail): void {
    $full = $root.'/'.$path;
    if (! is_file($full) || ! str_contains((string) file_get_contents($full), $needle)) {
        $fail("Missing [{$needle}] in [{$path}]");
    }
};
$assertExists = static function (string $root, string $path) use ($fail): void {
    if (! file_exists($root.'/'.$path)) {
        $fail("Expected generated path is missing: {$path}");
    }
};
$assertMissing = static function (string $root, string $path) use ($fail): void {
    if (file_exists($root.'/'.$path)) {
        $fail("Unexpected generated path exists: {$path}");
    }
};

$tmp = $fixture();
[$code, $output] = $run($tmp, $base);
if ($code !== 0) {
    $fail($output);
}
$assertContains($tmp, '"name": "mortalkiller/filament-example"', 'composer.json');
$assertContains($tmp, 'MortalKiller\\\\FilamentExample\\\\', 'composer.json');
$assertContains($tmp, "basePath = '/filament-example'", 'docs-site/astro.config.mjs');
$assertContains($tmp, "title: 'Filament Example'", 'docs-site/astro.config.mjs');
$assertContains($tmp, "description: 'Example Filament package.'", 'docs-site/astro.config.mjs');
$assertContains($tmp, 'https://docs.pedromonteiro.dev/filament-example/', 'README.md');
$assertContains($tmp, 'uses: mortalkiller/filament-package-template/.github/workflows/reusable-tests.yml@'.$workflowRef, '.github/workflows/tests.yml');
$assertContains($tmp, 'standard-ref: '.$workflowRef, '.github/workflows/standard.yml');
$assertContains($tmp, 'standard-ref: '.$workflowRef, '.github/workflows/docs-release.yml');
$assertContains($tmp, 'mortalkiller/filament-package-standard/blob/2.x/docs/package-standard.md', 'AGENTS.md');
$assertExists($tmp, 'src/FilamentExampleServiceProvider.php');
$assertExists($tmp, 'src/FilamentExamplePlugin.php');
$assertExists($tmp, 'tests/Unit/PluginTest.php');
$assertContains($tmp, "return 'filament-example';", 'src/FilamentExamplePlugin.php');
$assertContains($tmp, "->name('filament-example')", 'src/FilamentExampleServiceProvider.php');
$composer = json_decode((string) file_get_contents($tmp.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
if (($composer['require']['filament/filament'] ?? null) !== '^5.8.1') {
    $fail('Default plugin profile has the wrong Filament runtime dependency.');
}
if (($composer['require-dev']['mortalkiller/filament-package-standard'] ?? null) !== '^2.0') {
    $fail('Generated package is missing filament-package-standard ^2.0 in require-dev.');
}
if (isset($composer['require-dev']['rector/rector'])) {
    $fail('Rector leaked into the default generated package.');
}
foreach (['config', 'database', 'bin', 'workbench', 'package.json', 'playwright.config.mjs', '.github/workflows/browser-tests.yml', 'rector.php'] as $path) {
    $assertMissing($tmp, $path);
}
foreach (['.template', 'tools', 'tests/Template', 'tests/standard-checker', '.github/workflows/reusable-docs-release.yml'] as $removed) {
    $assertMissing($tmp, $removed);
}

$full = $fixture();
$fullInput = $base + [
    'with_config' => true,
    'with_database' => true,
    'with_views' => true,
    'with_translations' => true,
    'with_stubs' => true,
    'with_assets' => true,
    'with_workbench' => true,
    'with_browser_tests' => true,
    'with_rector' => true,
];
[$fullCode, $fullOutput] = $run($full, $fullInput);
if ($fullCode !== 0) {
    $fail($fullOutput);
}
foreach ([
    'config/filament-example.php',
    'database/migrations/create_filament_example_table.php.stub',
    'resources/views/.gitkeep',
    'resources/lang/en/filament-example.php',
    'resources/js/index.js',
    'resources/css/index.css',
    'resources/dist/.gitkeep',
    'stubs/.gitkeep',
    'bin/build.js',
    'workbench/app/Providers/WorkbenchServiceProvider.php',
    'workbench/app/Providers/Filament/AdminPanelProvider.php',
    'workbench/routes/web.php',
    'testbench.yaml',
    'playwright.config.mjs',
    'tests/Browser/package-smoke.spec.mjs',
    '.github/workflows/browser-tests.yml',
    'rector.php',
    'package.json',
] as $path) {
    $assertExists($full, $path);
}
$assertContains($full, '->plugin(\\MortalKiller\\FilamentExample\\FilamentExamplePlugin::make())', 'workbench/app/Providers/Filament/AdminPanelProvider.php');
$assertContains($full, 'reusable-browser-tests.yml@'.$workflowRef, '.github/workflows/browser-tests.yml');
$fullComposer = json_decode((string) file_get_contents($full.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
if (($fullComposer['autoload-dev']['psr-4']['Workbench\\'] ?? null) !== 'workbench/') {
    $fail('Workbench autoloading was not configured.');
}
if (($fullComposer['scripts']['serve'] ?? null) !== '@php vendor/bin/testbench serve') {
    $fail('Workbench serve script was not configured.');
}
if (! isset($fullComposer['require-dev']['rector/rector'])) {
    $fail('Rector was not retained when requested.');
}
$packageJson = json_decode((string) file_get_contents($full.'/package.json'), true, flags: JSON_THROW_ON_ERROR);
foreach (['build', 'dev', 'test:browser'] as $script) {
    if (! isset($packageJson['scripts'][$script])) {
        $fail("Missing package.json script: {$script}");
    }
}

$profiles = [
    'theme' => ['runtime' => 'filament/filament', 'plugin' => true, 'assets' => true],
    'forms' => ['runtime' => 'filament/forms', 'plugin' => false, 'assets' => false],
    'tables' => ['runtime' => 'filament/tables', 'plugin' => false, 'assets' => false],
    'library' => ['runtime' => 'filament/support', 'plugin' => false, 'assets' => false],
];
foreach ($profiles as $profile => $expectation) {
    $profileRoot = $fixture();
    [$profileCode, $profileOutput] = $run($profileRoot, $base + ['type' => $profile]);
    if ($profileCode !== 0) {
        $fail("Profile [{$profile}] failed: {$profileOutput}");
    }
    $profileComposer = json_decode((string) file_get_contents($profileRoot.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    if (($profileComposer['require'][$expectation['runtime']] ?? null) !== '^5.8.1') {
        $fail("Profile [{$profile}] has the wrong runtime package.");
    }
    if (file_exists($profileRoot.'/src/FilamentExamplePlugin.php') !== $expectation['plugin']) {
        $fail("Profile [{$profile}] has the wrong plugin scaffold state.");
    }
    if (file_exists($profileRoot.'/package.json') !== $expectation['assets']) {
        $fail("Profile [{$profile}] has the wrong asset scaffold state.");
    }
    $assertContains($profileRoot, '"filament_package":"'.$expectation['runtime'].'"', '.github/workflows/tests.yml');
}

[$secondCode, $secondOutput] = $run($tmp, $base);
if ($secondCode === 0 || ! str_contains($secondOutput, 'Package template has already been initialized.')) {
    $fail('Repeated initialization was not rejected safely.');
}
$partial = $fixture();
file_put_contents($partial.'/composer.json', str_replace('"name": "mortalkiller/filament-package-template"', '"name": "mortalkiller/filament-example"', (string) file_get_contents($partial.'/composer.json')));
file_put_contents($partial.'/.template/.initializing.json', json_encode($state, JSON_THROW_ON_ERROR));
[$partialCode, $partialOutput] = $run($partial, $base);
if ($partialCode !== 0 || file_exists($partial.'/.template')) {
    $fail('Matching partial initialization did not resume safely: '.$partialOutput);
}
$mismatch = $fixture();
file_put_contents($mismatch.'/.template/.initializing.json', json_encode($state, JSON_THROW_ON_ERROR));
[$mismatchCode, $mismatchOutput] = $run($mismatch, array_replace($base, ['slug' => 'filament-different']));
if ($mismatchCode === 0 || ! str_contains($mismatchOutput, 'Initialization is already in progress with different arguments.')) {
    $fail('Mismatched partial initialization was not rejected safely.');
}
$invalid = $fixture();
[$invalidCode, $invalidOutput] = $run($invalid, $base + ['type' => 'invalid']);
if ($invalidCode === 0 || ! str_contains($invalidOutput, 'The --type value must be one of')) {
    $fail('Invalid package profile was accepted.');
}
$mutable = $fixture();
[$refCode] = $run($mutable, array_replace($base, ['workflow_ref' => '2.x']));
if ($refCode === 0) {
    $fail('Mutable workflow reference was accepted.');
}

echo "initializer smoke test passed\n";
