<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard\Checks;

use JsonException;
use MortalKiller\PackageStandard\CheckResult;

final class ComposerMetadataCheck
{
    /**
     * @return array{slug:string,name:string}|null
     */
    public function run(string $root, CheckResult $result): ?array
    {
        $path = $root.'/composer.json';

        if (! is_file($path)) {
            return null;
        }

        try {
            $composer = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $result->fail('composer.invalid_json', 'composer.json must contain valid JSON.', 'composer.json');

            return null;
        }

        $name = (string) ($composer['name'] ?? '');
        if (! preg_match('/^mortalkiller\/(filament-[a-z0-9-]+)$/', $name, $matches)) {
            $result->fail(
                'composer.package_owner',
                'Package name must use the mortalkiller/filament-* namespace.',
                'composer.json',
            );

            return null;
        }

        $slug = $matches[1];

        if (($composer['type'] ?? null) !== 'library') {
            $result->fail('composer.type', 'Composer package type must be library.', 'composer.json');
        } else {
            $result->pass('composer.type', 'Composer package type is library.', 'composer.json');
        }

        $license = (string) ($composer['license'] ?? '');
        if ($license === 'MIT') {
            $result->pass('composer.license', 'Composer license is MIT.', 'composer.json');
        } elseif ($license === '') {
            $result->fail('composer.license', 'Composer license must be declared.', 'composer.json');
        } else {
            $result->warn(
                'composer.nonstandard_license',
                "Package uses [{$license}] instead of the standard MIT license; verify this is deliberate.",
                'composer.json',
            );
        }

        $expectedRepository = "https://github.com/mortalkiller/{$slug}";
        $homepage = $composer['homepage'] ?? null;
        if (is_string($homepage) && $homepage !== $expectedRepository) {
            $result->fail(
                'composer.homepage',
                "Composer homepage should be {$expectedRepository}.",
                'composer.json',
            );
        }

        $support = $composer['support'] ?? null;
        if (is_array($support)) {
            $expectedIssues = $expectedRepository.'/issues';
            if (($support['issues'] ?? null) !== $expectedIssues) {
                $result->fail('composer.support_issues', "Composer support.issues should be {$expectedIssues}.", 'composer.json');
            }
            if (($support['source'] ?? null) !== $expectedRepository) {
                $result->fail('composer.support_source', "Composer support.source should be {$expectedRepository}.", 'composer.json');
            }
        }

        $result->pass('composer.identity', "Composer package identity is {$name}.", 'composer.json');

        return ['slug' => $slug, 'name' => $name];
    }
}
