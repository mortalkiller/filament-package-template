<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Template;

use PHPUnit\Framework\TestCase;

final class InitializePackageTest extends TestCase
{
    public function test_template_initializer_rewrites_identity_and_rejects_second_run(): void
    {
        $command = 'php '.escapeshellarg(__DIR__.'/run.php').' 2>&1';
        exec($command, $output, $exitCode);

        self::assertSame(0, $exitCode, implode(PHP_EOL, $output));
        self::assertStringContainsString('initializer smoke test passed', implode(PHP_EOL, $output));
    }
}
