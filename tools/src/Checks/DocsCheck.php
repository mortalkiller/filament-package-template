<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use MortalKiller\PackageStandard\CheckResult;

final class DocsCheck
{
    public function run(string $root, string $slug, CheckResult $result): void
    {
        $path = $root.'/docs-site/astro.config.mjs';
        if (! is_file($path)) {
            return;
        }

        $content = (string) file_get_contents($path);

        $literalChecks = [
            'docs.site' => 'https://docs.pedromonteiro.dev',
            'docs.repository_url' => "https://github.com/mortalkiller/{$slug}",
            'docs.personal_site' => 'https://pedromonteiro.dev',
        ];

        foreach ($literalChecks as $code => $needle) {
            if (str_contains($content, $needle)) {
                $result->pass($code, "Documentation configuration contains {$needle}.", 'docs-site/astro.config.mjs');
            } else {
                $result->fail($code, "Documentation configuration must contain {$needle}.", 'docs-site/astro.config.mjs');
            }
        }

        $expectedBase = '/'.$slug;
        $quotedBase = preg_quote($expectedBase, '/');
        $hasBase = preg_match(
            "/(?:const\\s+basePath\\s*=|base\\s*:)\\s*['\"]{$quotedBase}['\"]/",
            $content,
        ) === 1;

        if ($hasBase) {
            $result->pass('docs.base_path', "Documentation base path is {$expectedBase}.", 'docs-site/astro.config.mjs');
        } else {
            $result->fail('docs.base_path', "Documentation base path must be {$expectedBase}.", 'docs-site/astro.config.mjs');
        }
    }
}
