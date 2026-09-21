<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$checker = $root.'/tools/package-standard-check.php';

if (! is_file($checker)) {
    fwrite(STDERR, "RED: standard checker missing\n");
    exit(1);
}

echo "checker harness ready\n";
