<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use MortalKiller\PackageStandard\CheckResult;

final class RequiredFilesCheck
{
    /** @var list<string> */
    private const REQUIRED = [
        'README.md',
        'CONTRIBUTING.md',
        'SECURITY.md',
        'LICENSE.md',
        'composer.json',
        'src',
        'tests',
        'docs/roadmap.md',
        'docs-site/astro.config.mjs',
        '.github/dependabot.yml',
        '.github/ISSUE_TEMPLATE/bug_report.yml',
        '.github/ISSUE_TEMPLATE/feature_request.yml',
        '.github/PULL_REQUEST_TEMPLATE.md',
        '.github/workflows/tests.yml',
        '.github/workflows/quality.yml',
        '.github/workflows/docs.yml',
        '.github/workflows/standard.yml',
    ];

    public function run(string $root, CheckResult $result): void
    {
        $missing = [];

        foreach (self::REQUIRED as $path) {
            if (! file_exists($root.'/'.$path)) {
                $missing[] = $path;
                $result->fail(
                    'structure.required_file',
                    "Required package-standard path is missing: {$path}.",
                    $path,
                );
            }
        }

        if ($missing === []) {
            $result->pass('structure.required_files', 'All required package-standard paths exist.');
        }
    }
}
