<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\StandardChecker;

use PHPUnit\Framework\TestCase;

final class CheckerTest extends TestCase
{
    public function test_standard_checker_fixture_suite(): void
    {
        $command = 'php '.escapeshellarg(dirname(__DIR__).'/standard-checker/run.php').' 2>&1';
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
        self::assertStringContainsString('standard checker fixture suite passed', implode(PHP_EOL, $output));
    }
}
