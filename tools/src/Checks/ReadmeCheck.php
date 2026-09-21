<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use MortalKiller\PackageStandard\CheckResult;

final class ReadmeCheck
{
    public function run(string $root, string $slug, CheckResult $result, bool $skipPlumb): void
    {
        $path = $root.'/README.md';
        if (! is_file($path)) {
            return;
        }

        $content = (string) file_get_contents($path);
        $docsUrl = "https://docs.pedromonteiro.dev/{$slug}/";

        if (str_contains($content, $docsUrl)) {
            $result->pass('readme.docs_url', 'README links to the canonical documentation URL.', 'README.md');
        } else {
            $result->fail('readme.docs_url', "README must link to {$docsUrl}.", 'README.md');
        }

        if ($skipPlumb) {
            $result->warn('readme.plumb_skipped', 'PlumbPHP badge validation skipped for template infrastructure.', 'README.md');

            return;
        }

        $required = ['scanned', 'ecosystem', 'maintenance', 'security', 'composite'];
        $missing = [];
        foreach ($required as $badge) {
            $needle = "https://plumbphp.dev/badges/mortalkiller/{$slug}/{$badge}.svg";
            if (! str_contains($content, $needle)) {
                $missing[] = $badge;
            }
        }

        if ($missing === []) {
            $result->pass('readme.plumb_badges', 'README contains the required PlumbPHP badges.', 'README.md');
        } else {
            $result->fail(
                'readme.plumb_badges',
                'README is missing PlumbPHP badges: '.implode(', ', $missing).'.',
                'README.md',
            );
        }
    }
}
