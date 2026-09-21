<?php

declare(strict_types=1);

$repo = dirname(__DIR__, 2);
$initializer = $repo.'/.template/initialize-package.php';
$workflowRef = str_repeat('a', 40);
$state = [
    'slug' => 'filament-example',
    'title' => 'Filament Example',
    'namespace' => 'MortalKiller\\FilamentExample',
    'description' => 'Example Filament package.',
    'workflow_ref' => $workflowRef,
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
        $args[] = '--'.str_replace('_', '-', $key).'='.escapeshellarg($value);
    }
    $command = 'PACKAGE_TEMPLATE_ROOT='.escapeshellarg($root).' '.escapeshellarg(PHP_BINARY).' '.escapeshellarg($initializer).' '.implode(' ', $args).' 2>&1';
    exec($command, $output, $code);

    return [$code, implode("\n", $output)];
};
$tmp = $fixture();
[$code, $output] = $run($tmp, $state);
if ($code !== 0) {
    $fail($output);
}
$assertContains = static function (string $needle, string $path) use ($tmp, $fail): void {
    $full = $tmp.'/'.$path;
    if (! is_file($full) || ! str_contains((string) file_get_contents($full), $needle)) {
        $fail("Missing [{$needle}] in [{$path}]");
    }
};
$assertContains('"name": "mortalkiller/filament-example"', 'composer.json');
$assertContains('MortalKiller\\\\FilamentExample\\\\', 'composer.json');
$assertContains("basePath = '/filament-example'", 'docs-site/astro.config.mjs');
$assertContains("title: 'Filament Example'", 'docs-site/astro.config.mjs');
$assertContains("description: 'Example Filament package.'", 'docs-site/astro.config.mjs');
$assertContains('https://docs.pedromonteiro.dev/filament-example/', 'README.md');
$assertContains('uses: mortalkiller/filament-package-template/.github/workflows/reusable-tests.yml@'.$workflowRef, '.github/workflows/tests.yml');
$assertContains('standard-ref: '.$workflowRef, '.github/workflows/standard.yml');
$assertContains('standard-ref: '.$workflowRef, '.github/workflows/docs-release.yml');
$assertContains('mortalkiller/filament-package-template/blob/1.x/docs/package-standard.md', 'AGENTS.md');
if (! is_file($tmp.'/src/FilamentExampleServiceProvider.php')) {
    $fail('Renamed service provider is missing.');
}
$composer = json_decode((string) file_get_contents($tmp.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
if (in_array('package-template', $composer['keywords'] ?? [], true)) {
    $fail('Template-only Composer keyword leaked into the generated package.');
}
if (str_contains((string) file_get_contents($tmp.'/SECURITY.md'), 'This template does not itself publish a runtime package')) {
    $fail('Template-specific security text leaked into the generated package.');
}
foreach (['.template', 'tools', 'skills', 'tests/Template', 'tests/standard-checker', '.github/workflows/reusable-docs-release.yml'] as $removed) {
    if (file_exists($tmp.'/'.$removed)) {
        $fail('Template-only path still exists: '.$removed);
    }
}
[$secondCode, $secondOutput] = $run($tmp, $state);
if ($secondCode === 0 || ! str_contains($secondOutput, 'Package template has already been initialized.')) {
    $fail('Repeated initialization was not rejected safely.');
}
$partial = $fixture();
file_put_contents($partial.'/composer.json', str_replace('"name": "mortalkiller/filament-package-template"', '"name": "mortalkiller/filament-example"', (string) file_get_contents($partial.'/composer.json')));
file_put_contents($partial.'/.template/.initializing.json', json_encode($state, JSON_THROW_ON_ERROR));
[$partialCode, $partialOutput] = $run($partial, $state);
if ($partialCode !== 0 || file_exists($partial.'/.template')) {
    $fail('Matching partial initialization did not resume safely: '.$partialOutput);
}
$mismatch = $fixture();
file_put_contents($mismatch.'/.template/.initializing.json', json_encode($state, JSON_THROW_ON_ERROR));
[$mismatchCode, $mismatchOutput] = $run($mismatch, array_replace($state, ['slug' => 'filament-different']));
if ($mismatchCode === 0 || ! str_contains($mismatchOutput, 'Initialization is already in progress with different arguments.')) {
    $fail('Mismatched partial initialization was not rejected safely.');
}
[$refCode] = $run($mismatch, array_replace($state, ['workflow_ref' => '1.x']));
if ($refCode === 0) {
    $fail('Mutable workflow reference was accepted.');
}

echo "initializer smoke test passed\n";
