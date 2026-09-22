<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPackageTemplate;

use Filament\Contracts\Plugin;
use Filament\Panel;

final class FilamentPackageTemplatePlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-package-template';
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(self::class)->getId());

        return $plugin;
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
