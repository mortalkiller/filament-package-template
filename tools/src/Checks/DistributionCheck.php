<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use MortalKiller\PackageStandard\CheckResult;

final class DistributionCheck
{
    /** @var list<string> */
    private const FORBIDDEN_PREFIXES = [
        '.github/',
        'tests/',
        'docs/superpowers/',
        'vendor/',
        'node_modules/',
        'playwright-report/',
        'test-results/',
    ];

    /** @var list<string> */
    private const FORBIDDEN_FILES = [
        'composer.lock',
    ];

    public function run(string $root, CheckResult $result): void
    {
        if (! is_dir($root.'/.git')) {
            $result->warn('distribution.git_unavailable', 'Distribution archive validation requires a Git repository.');

            return;
        }

        $tar = tempnam(sys_get_temp_dir(), 'package-standard-');
        if ($tar === false) {
            $result->warn('distribution.tempfile', 'Unable to create a temporary archive for distribution validation.');

            return;
        }

        try {
            [$archiveCode] = $this->runProcess(['git', '-C', $root, 'archive', '--format=tar', '--output='.$tar, 'HEAD']);
            if ($archiveCode !== 0) {
                $result->warn('distribution.archive_unavailable', 'Unable to build git archive for distribution validation.');

                return;
            }

            [$listCode, $output] = $this->runProcess(['tar', '-tf', $tar]);
            if ($listCode !== 0) {
                $result->warn('distribution.archive_unreadable', 'Unable to inspect generated distribution archive.');

                return;
            }

            $paths = array_filter(array_map('trim', explode("\n", $output)));
            $failures = 0;

            foreach ($paths as $path) {
                if (in_array($path, self::FORBIDDEN_FILES, true) || $this->hasForbiddenPrefix($path)) {
                    $failures++;
                    $result->fail(
                        'distribution.development_path',
                        "Development-only path is included in the Composer distribution archive: {$path}.",
                        $path,
                    );
                }
            }

            if ($failures === 0) {
                $result->pass('distribution.clean', 'Distribution archive excludes configured development-only paths.');
            }
        } finally {
            @unlink($tar);
        }
    }

    private function hasForbiddenPrefix(string $path): bool
    {
        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $command
     * @return array{int,string,string}
     */
    private function runProcess(array $command): array
    {
        $pipes = [];
        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
        );

        if (! is_resource($process)) {
            return [1, '', 'Unable to start process.'];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [
            proc_close($process),
            is_string($stdout) ? $stdout : '',
            is_string($stderr) ? $stderr : '',
        ];
    }
}
