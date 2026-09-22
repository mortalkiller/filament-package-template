<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use FilesystemIterator;
use MortalKiller\PackageStandard\CheckResult;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class WorkflowSecurityCheck
{
    public function run(string $root, CheckResult $result): void
    {
        $directory = $root.'/.github/workflows';

        if (! is_dir($directory)) {
            return;
        }

        $unsafeSecrets = [];
        $legacySelfRepository = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), ['yml', 'yaml'], true)) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            if (! is_string($content)) {
                continue;
            }

            $relative = ltrim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR);

            if (preg_match('/^\s*secrets:\s*inherit\s*$/m', $content)) {
                $unsafeSecrets[] = $relative;
                $result->fail(
                    'workflow.secrets_inherit',
                    'Reusable workflows must receive only the secrets they require. Do not use unconditional secrets: inherit.',
                    $relative,
                );
            }

            if (preg_match('/^\s*uses:\s+\.\//m', $content)) {
                $legacySelfRepository[] = $relative;
                $result->fail(
                    'workflow.self_repository',
                    'Use GitHub self-repository syntax ($/...) instead of workspace-relative uses: ./... references.',
                    $relative,
                );
            }
        }

        if ($unsafeSecrets === []) {
            $result->pass('workflow.secret_scope', 'GitHub Actions workflows do not unconditionally inherit caller secrets.');
        }

        if ($legacySelfRepository === []) {
            $result->pass('workflow.self_repository', 'GitHub Actions use dedicated self-repository syntax for in-repository actions and workflows.');
        }
    }
}
