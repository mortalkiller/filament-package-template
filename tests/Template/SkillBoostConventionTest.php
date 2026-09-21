<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Template;

use PHPUnit\Framework\TestCase;

final class SkillBoostConventionTest extends TestCase
{
    public function test_skill_uses_laravel_boost_third_party_package_convention(): void
    {
        $root = dirname(__DIR__, 2);
        $skill = $root.'/resources/boost/skills/developing-filament-packages';

        self::assertDirectoryExists($skill);
        self::assertFileExists($skill.'/SKILL.md');
        self::assertFileExists($skill.'/references/api-review.md');
        self::assertFileExists($skill.'/references/release-checklist.md');
        self::assertFileExists($skill.'/references/standard-summary.md');

        self::assertDirectoryDoesNotExist($root.'/skills/developing-filament-packages');

        $attributes = (string) file_get_contents($root.'/.gitattributes');
        self::assertStringNotContainsString('/resources export-ignore', $attributes);
        self::assertStringNotContainsString('/resources/boost export-ignore', $attributes);
    }
}
