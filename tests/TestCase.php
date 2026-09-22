<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate\Tests;

use MortalKiller\FilamentPackageTemplate\FilamentPackageTemplateServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentPackageTemplateServiceProvider::class,
        ];
    }
}
