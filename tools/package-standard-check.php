<?php

declare(strict_types=1);

use MortalKiller\PackageStandard\CheckResult;
use MortalKiller\PackageStandard\Checks\ComposerMetadataCheck;
use MortalKiller\PackageStandard\Checks\DistributionCheck;
use MortalKiller\PackageStandard\Checks\DocsCheck;
use MortalKiller\PackageStandard\Checks\PublicContentCheck;
use MortalKiller\PackageStandard\Checks\ReadmeCheck;
use MortalKiller\PackageStandard\Checks\RequiredFilesCheck;

require __DIR__.'/src/Finding.php';
require __DIR__.'/src/CheckResult.php';
require __DIR__.'/src/Checks/RequiredFilesCheck.php';
require __DIR__.'/src/Checks/ComposerMetadataCheck.php';
require __DIR__.'/src/Checks/ReadmeCheck.php';
require __DIR__.'/src/Checks/DocsCheck.php';
require __DIR__.'/src/Checks/PublicContentCheck.php';
require __DIR__.'/src/Checks/DistributionCheck.php';

$args = array_slice($argv, 1);
$json = false;
$skipPlumb = false;
$root = null;

foreach ($args as $arg) {
    if ($arg === '--json') {
        $json = true;

        continue;
    }

    if ($arg === '--skip-plumb') {
        $skipPlumb = true;

        continue;
    }

    if (str_starts_with($arg, '--')) {
        fwrite(STDERR, "Unknown option: {$arg}".PHP_EOL);
        exit(2);
    }

    if ($root !== null) {
        fwrite(STDERR, 'Only one repository path may be supplied.'.PHP_EOL);
        exit(2);
    }

    $root = $arg;
}

$root ??= getcwd();
$resolved = realpath((string) $root);

if ($resolved === false || ! is_dir($resolved)) {
    fwrite(STDERR, "Repository path does not exist: {$root}".PHP_EOL);
    exit(2);
}

$result = new CheckResult;

(new RequiredFilesCheck)->run($resolved, $result);
$identity = (new ComposerMetadataCheck)->run($resolved, $result);

if ($identity !== null) {
    (new ReadmeCheck)->run($resolved, $identity['slug'], $result, $skipPlumb);
    (new DocsCheck)->run($resolved, $identity['slug'], $result);
}

(new PublicContentCheck)->run($resolved, $result);
(new DistributionCheck)->run($resolved, $result);

if ($json) {
    echo json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
} else {
    echo $result->renderText();
}

exit($result->hasFailures() ? 1 : 0);
