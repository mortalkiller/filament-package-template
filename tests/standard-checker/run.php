<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checker = $root.'/tools/package-standard-check.php';

if (is_file($checker) === false) {
    fwrite(STDERR, "standard checker missing\n");
    exit(1);
}

$makeFixture = static function (string $slug, array $options = []): string {
    $tmp = sys_get_temp_dir().'/standard-check-'.bin2hex(random_bytes(4));
    mkdir($tmp, 0777, true);

    $write = static function (string $path, string $content) use ($tmp): void {
        @mkdir(dirname($tmp.'/'.$path), 0777, true);
        file_put_contents($tmp.'/'.$path, $content);
    };

    $composerName = $options['composer_name'] ?? "mortalkiller/{$slug}";
    $composer = [
        'name' => $composerName,
        'description' => 'Synthetic package fixture.',
        'homepage' => "https://github.com/mortalkiller/{$slug}",
        'type' => 'library',
        'license' => 'MIT',
        'support' => [
            'issues' => "https://github.com/mortalkiller/{$slug}/issues",
            'source' => "https://github.com/mortalkiller/{$slug}",
        ],
    ];

    $readme = "# Fixture\n\nhttps://docs.pedromonteiro.dev/{$slug}/\n";
    foreach (['scanned', 'ecosystem', 'maintenance', 'security', 'composite'] as $badge) {
        $readme .= "https://plumbphp.dev/badges/mortalkiller/{$slug}/{$badge}.svg\n";
    }
    $readme .= $options['readme_append'] ?? '';

    $write('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    $write('README.md', $readme);
    $write('CONTRIBUTING.md', "# Contributing\n");
    $write('SECURITY.md', "# Security\n");
    $write('LICENSE.md', "# MIT\n");
    $write('docs/roadmap.md', "# Roadmap\n");
    $write('docs-site/astro.config.mjs', $options['astro'] ?? "site: 'https://docs.pedromonteiro.dev'; const basePath = '/{$slug}'; const repositoryUrl = 'https://github.com/mortalkiller/{$slug}'; const personal = 'https://pedromonteiro.dev';\n");
    $write('.github/dependabot.yml', "version: 2\n");
    $write('.github/ISSUE_TEMPLATE/bug_report.yml', "name: Bug report\n");
    $write('.github/ISSUE_TEMPLATE/feature_request.yml', "name: Feature request\n");
    $write('.github/PULL_REQUEST_TEMPLATE.md', "# Pull request\n");
    $write('.github/workflows/tests.yml', "name: tests\n");
    $write('.github/workflows/quality.yml', "name: quality\n");
    $write('.github/workflows/docs.yml', "name: docs\n");
    $write('.github/workflows/standard.yml', "name: standard\n");
    $write('src/.gitkeep', '');
    $write('tests/.gitkeep', '');

    if (($options['git'] ?? true) === true) {
        $write('.gitattributes', "/.github export-ignore\n/tests export-ignore\ncomposer.lock export-ignore\n");
        exec('git -C '.escapeshellarg($tmp).' init -q');
        exec('git -C '.escapeshellarg($tmp).' config user.email fixture@example.com');
        exec('git -C '.escapeshellarg($tmp).' config user.name Fixture');
        exec('git -C '.escapeshellarg($tmp).' add .');
        exec('git -C '.escapeshellarg($tmp).' commit -qm fixture');
    }

    return $tmp;
};

$run = static function (string $path, bool $json = false) use ($checker): array {
    $command = 'php '.escapeshellarg($checker).' '.($json ? '--json ' : '').escapeshellarg($path).' 2>&1';
    exec($command, $output, $code);

    return [$code, implode("\n", $output)];
};

$valid = $makeFixture('filament-example');
[$validCode, $validOutput] = $run($valid);
if ($validCode !== 0 || str_contains($validOutput, 'FAIL 0') === false) {
    fwrite(STDERR, "Valid fixture failed:\n{$validOutput}\n");
    exit(2);
}

$badOwner = $makeFixture('filament-example', ['composer_name' => 'someone/filament-example']);
[$ownerCode, $ownerOutput] = $run($badOwner);
if ($ownerCode !== 1 || str_contains($ownerOutput, 'composer.package_owner') === false) {
    fwrite(STDERR, "Wrong-owner fixture did not fail correctly:\n{$ownerOutput}\n");
    exit(3);
}

$badDocs = $makeFixture('filament-example', [
    'astro' => "site: 'https://docs.pedromonteiro.dev'; const basePath = '/wrong'; const repositoryUrl = 'https://github.com/mortalkiller/filament-example'; const personal = 'https://pedromonteiro.dev';\n",
]);
[$docsCode, $docsOutput] = $run($badDocs);
if ($docsCode !== 1 || str_contains($docsOutput, 'docs.base_path') === false) {
    fwrite(STDERR, "Wrong-base fixture did not fail correctly:\n{$docsOutput}\n");
    exit(4);
}

$secret = $makeFixture('filament-example', [
    'readme_append' => "\n-----BEGIN OPENSSH PRIVATE KEY-----\nsynthetic-test-value\n",
]);
[$secretCode, $secretOutput] = $run($secret);
if ($secretCode !== 1 || str_contains($secretOutput, 'public_content.sensitive_pattern') === false) {
    fwrite(STDERR, "Sensitive-content fixture did not fail correctly:\n{$secretOutput}\n");
    exit(5);
}

$safe = $makeFixture('filament-example', [
    'readme_append' => "\n127.0.0.1\nexample.com\n/var/www/app\nDOCS_HOST\n",
]);
[$safeCode, $safeOutput] = $run($safe);
if ($safeCode !== 0) {
    fwrite(STDERR, "Safe-example fixture produced a false positive:\n{$safeOutput}\n");
    exit(6);
}

$hostPath = $makeFixture('filament-example', [
    'readme_append' => "\n/opt/example-company/internal/app\n",
]);
[$hostPathCode, $hostPathOutput] = $run($hostPath);
if ($hostPathCode !== 1 || str_contains($hostPathOutput, 'public_content.sensitive_pattern') === false) {
    fwrite(STDERR, "Generic host-path fixture did not fail correctly:\n{$hostPathOutput}\n");
    exit(61);
}

$ssh = $makeFixture('filament-example', [
    'readme_append' => "\nssh -p 2222 -i ~/.ssh/deploy_key deploy@203.0.113.10\n",
]);
[$sshCode, $sshOutput] = $run($ssh);
if ($sshCode !== 1 || str_contains($sshOutput, 'public_content.sensitive_pattern') === false) {
    fwrite(STDERR, "Literal SSH infrastructure fixture did not fail correctly:\n{$sshOutput}\n");
    exit(62);
}

$publicAsset = $makeFixture('filament-example');
mkdir($publicAsset.'/docs-site/public', 0777, true);
file_put_contents($publicAsset.'/docs-site/public/deploy.txt', "sk_live_1234567890abcdef\n");
[$publicAssetCode, $publicAssetOutput] = $run($publicAsset);
if ($publicAssetCode !== 1 || str_contains($publicAssetOutput, 'public_content.sensitive_pattern') === false) {
    fwrite(STDERR, "Public docs asset fixture did not fail correctly:\n{$publicAssetOutput}\n");
    exit(63);
}


$unsafeWorkflow = $makeFixture('filament-example');
$unsafeWorkflowRef = str_repeat('a', 40);
$unsafeWorkflowContent = str_replace(
    '__WORKFLOW_REF__',
    $unsafeWorkflowRef,
    <<<'YAML'
name: Release docs
jobs:
  publish:
    uses: owner/repo/.github/workflows/reusable.yml@__WORKFLOW_REF__
    secrets: inherit

YAML,
);
file_put_contents(
    $unsafeWorkflow.'/.github/workflows/docs-release.yml',
    $unsafeWorkflowContent,
);
[$unsafeWorkflowCode, $unsafeWorkflowOutput] = $run($unsafeWorkflow);
if ($unsafeWorkflowCode !== 1 || str_contains($unsafeWorkflowOutput, 'workflow.secrets_inherit') === false) {
    fwrite(STDERR, "Unscoped workflow secrets were not rejected:\n{$unsafeWorkflowOutput}\n");
    exit(64);
}

$archive = $makeFixture('filament-example');
mkdir($archive.'/playwright-report', 0777, true);
file_put_contents($archive.'/playwright-report/report.html', "<html></html>\n");
exec('git -C '.escapeshellarg($archive).' add playwright-report/report.html');
exec('git -C '.escapeshellarg($archive).' commit -qm forbidden');
[$archiveCode, $archiveOutput] = $run($archive);
if ($archiveCode !== 1 || str_contains($archiveOutput, 'distribution.development_path') === false) {
    fwrite(STDERR, "Distribution fixture did not fail correctly:\n{$archiveOutput}\n");
    exit(7);
}

$worktreeSource = $makeFixture('filament-example');
$worktree = sys_get_temp_dir().'/standard-check-worktree-'.bin2hex(random_bytes(4));
exec(
    'git -C '.escapeshellarg($worktreeSource).' worktree add -q -b checker-worktree '.escapeshellarg($worktree),
    $worktreeCommandOutput,
    $worktreeCommandCode,
);
if ($worktreeCommandCode !== 0) {
    fwrite(STDERR, "Unable to create worktree fixture.\n");
    exit(71);
}
[$worktreeCode, $worktreeOutput] = $run($worktree);
if (
    $worktreeCode !== 0
    || str_contains($worktreeOutput, 'distribution.git_unavailable')
    || str_contains($worktreeOutput, 'distribution.clean') === false
) {
    fwrite(STDERR, "Worktree distribution fixture did not validate correctly:\n{$worktreeOutput}\n");
    exit(72);
}

[$jsonCode, $jsonOutput] = $run($valid, true);
$json = json_decode($jsonOutput, true);
if ($jsonCode !== 0 || is_array($json) === false || ($json['summary']['fail'] ?? null) !== 0) {
    fwrite(STDERR, "JSON output is invalid:\n{$jsonOutput}\n");
    exit(8);
}

echo "standard checker fixture suite passed\n";
