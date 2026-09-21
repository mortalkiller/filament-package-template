<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Template;

use PHPUnit\Framework\TestCase;

final class SkillInstallerTest extends TestCase
{
    public function test_skill_installer_installs_and_protects_existing_skill(): void
    {
        $root = dirname(__DIR__, 2);
        $installer = $root.'/skills/developing-filament-packages/install.sh';

        self::assertFileExists($installer);

        $target = sys_get_temp_dir().'/filament-skill-install-'.bin2hex(random_bytes(4));
        mkdir($target, 0777, true);

        $install = 'bash '.escapeshellarg($installer).' '.escapeshellarg($target).' 2>&1';
        exec($install, $firstOutput, $firstCode);

        self::assertSame(0, $firstCode, implode(PHP_EOL, $firstOutput));
        self::assertFileExists($target.'/developing-filament-packages/SKILL.md');

        file_put_contents(
            $target.'/developing-filament-packages/SKILL.md',
            "---\nname: different-skill\n---\n",
        );

        exec($install, $secondOutput, $secondCode);

        self::assertNotSame(0, $secondCode);
        self::assertStringContainsString(
            'already exists',
            implode(PHP_EOL, $secondOutput),
        );

        $force = $install.' --force';
        exec($force, $forceOutput, $forceCode);

        self::assertSame(0, $forceCode, implode(PHP_EOL, $forceOutput));

        $installed = (string) file_get_contents(
            $target.'/developing-filament-packages/SKILL.md',
        );

        self::assertStringContainsString(
            'name: developing-filament-packages',
            $installed,
        );
    }
}
