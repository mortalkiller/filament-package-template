<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class FilamentPackageTemplateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-package-template');
    }
}
