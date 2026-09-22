<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests\Unit;

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
}
