<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Unit;

use MortalKiller\FilamentPackageTemplate\FilamentPackageTemplatePlugin;
use MortalKiller\FilamentPackageTemplate\FilamentPackageTemplateServiceProvider;
use MortalKiller\FilamentPackageTemplate\Tests\TestCase;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class PackageBootTest extends TestCase
{
    public function test_service_provider_boots(): void
    {
        self::assertTrue(app()->providerIsLoaded(FilamentPackageTemplateServiceProvider::class));
        self::assertTrue(is_subclass_of(FilamentPackageTemplateServiceProvider::class, PackageServiceProvider::class));
    }

    public function test_plugin_exposes_the_expected_filament_contract(): void
    {
        $plugin = FilamentPackageTemplatePlugin::make();

        self::assertSame('filament-package-template', $plugin->getId());
        self::assertInstanceOf(FilamentPackageTemplatePlugin::class, $plugin);
    }
}
