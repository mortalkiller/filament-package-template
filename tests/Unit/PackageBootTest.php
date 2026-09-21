<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Unit;

use MortalKiller\FilamentPackageTemplate\FilamentPackageTemplateServiceProvider;
use Orchestra\Testbench\TestCase;

final class PackageBootTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [FilamentPackageTemplateServiceProvider::class];
    }

    public function test_service_provider_boots(): void
    {
        self::assertTrue(app()->providerIsLoaded(FilamentPackageTemplateServiceProvider::class));
    }
}
