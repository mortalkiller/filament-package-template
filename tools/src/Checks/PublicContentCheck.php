<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use FilesystemIterator;
use MortalKiller\PackageStandard\CheckResult;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PublicContentCheck
{
    /** @var array<string,string> */
    private const PATTERNS = [
        'OpenSSH private key' => '/-----BEGIN OPENSSH PRIVATE KEY-----/',
        'GitHub token' => '/\bgh[pousr]_[A-Za-z0-9]{20,}\b/',
        'AWS access key' => '/\bAKIA[A-Z0-9]{16}\b/',
        'Stripe live secret' => '/\bsk_live_[A-Za-z0-9]{16,}\b/',
        'production webserver path' => '#/opt/webserver/#',
        'literal SSH infrastructure' => '/\bssh\s+(?:[^\n]*\s)?-p\s+\d{2,5}\s+(?:[^\s@]+@)?(?:\d{1,3}\.){3}\d{1,3}\b/i',
    ];

    /** @var list<string> */
    private const ROOT_FILES = ['README.md', 'CONTRIBUTING.md', 'SECURITY.md'];

    /** @var list<string> */
    private const DIRECTORIES = ['docs', 'docs-site/src', 'docs-site/public', '.github'];

    public function run(string $root, CheckResult $result): void
    {
        $files = [];

        foreach (self::ROOT_FILES as $relative) {
            if (is_file($root.'/'.$relative)) {
                $files[$relative] = $root.'/'.$relative;
            }
        }

        foreach (self::DIRECTORIES as $relative) {
            $directory = $root.'/'.$relative;
            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveCallbackFilterIterator(
                    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                    static function (SplFileInfo $file): bool {
                        if ($file->isDir() && in_array($file->getFilename(), ['node_modules', 'dist', '.mortal-package-standard'], true)) {
                            return false;
                        }

                        return true;
                    },
                ),
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                    continue;
                }

                $relativePath = ltrim(str_replace($root, '', $file->getPathname()), '/');
                $files[$relativePath] = $file->getPathname();
            }
        }

        $failures = 0;
        foreach ($files as $relative => $absolute) {
            $content = file_get_contents($absolute);
            if (! is_string($content) || str_contains($content, "\0")) {
                continue;
            }

            foreach (self::PATTERNS as $category => $pattern) {
                if (preg_match($pattern, $content) !== 1) {
                    continue;
                }

                $failures++;
                $result->fail(
                    'public_content.sensitive_pattern',
                    "Public content contains a {$category} pattern; replace it with a neutral example or secret reference.",
                    $relative,
                );
            }
        }

        if ($failures === 0) {
            $result->pass('public_content.clean', 'No configured sensitive public-content patterns were found.');
        }
    }
}
