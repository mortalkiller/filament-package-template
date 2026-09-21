<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Template;

use PHPUnit\Framework\TestCase;

final class RepositoryFilesTest extends TestCase
{
    public function test_repository_standard_files_are_present_and_consistent(): void
    {
        $command = 'php '.escapeshellarg(__DIR__.'/repository-files.php').' 2>&1';
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
        self::assertStringContainsString('repository files smoke test passed', implode(PHP_EOL, $output));
    }
}
