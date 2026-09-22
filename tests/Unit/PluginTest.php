<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Unit;

use MortalKiller\FilamentPackageTemplate\FilamentPackageTemplatePlugin;
use MortalKiller\FilamentPackageTemplate\Tests\TestCase;

final class PluginTest extends TestCase
{
    public function test_plugin_exposes_the_expected_filament_contract(): void
    {
        $plugin = FilamentPackageTemplatePlugin::make();

        self::assertSame('filament-package-template', $plugin->getId());
        self::assertInstanceOf(FilamentPackageTemplatePlugin::class, $plugin);
    }
}
