<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Template;

use PHPUnit\Framework\TestCase;

final class SkillDefinitionTest extends TestCase
{
    public function test_developing_filament_packages_skill_matches_standard_contract(): void
    {
        $root = dirname(__DIR__, 2);
        $path = $root.'/resources/boost/skills/developing-filament-packages/SKILL.md';

        self::assertFileExists($path);

        $content = (string) file_get_contents($path);

        self::assertStringStartsWith("---\n", $content);
        self::assertStringContainsString('name: developing-filament-packages', $content);
        self::assertMatchesRegularExpression('/description:\s+Use when /', $content);
        self::assertStringContainsString('docs/package-standard.md', $content);
        self::assertStringContainsString('PlumbPHP', $content);
        self::assertStringContainsString('Ecosystem 100', $content);
        self::assertStringContainsString('Maintenance 100', $content);
        self::assertStringContainsString('Security 100', $content);
        self::assertStringContainsString('Composite 100', $content);
        self::assertStringContainsString('main', $content);
        self::assertStringContainsString('Laravel Boost', $content);
        self::assertStringContainsString('resources/boost/skills', $content);
        self::assertStringContainsString('*.x', $content);

        $words = preg_split('/\s+/', trim(strip_tags($content)));
        self::assertIsArray($words);
        self::assertLessThanOrEqual(500, count(array_filter($words)));

        foreach ([
            'references/standard-summary.md',
            'references/api-review.md',
            'references/release-checklist.md',
        ] as $relative) {
            self::assertFileExists($root.'/resources/boost/skills/developing-filament-packages/'.$relative);
        }
    }
}
