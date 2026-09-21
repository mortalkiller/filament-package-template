<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard;

final class CheckResult
{
    /** @var list<Finding> */
    private array $findings = [];

    public function add(Finding $finding): void
    {
        $this->findings[] = $finding;
    }

    public function pass(string $code, string $message, ?string $path = null): void
    {
        $this->add(new Finding('PASS', $code, $message, $path));
    }

    public function warn(string $code, string $message, ?string $path = null): void
    {
        $this->add(new Finding('WARN', $code, $message, $path));
    }

    public function fail(string $code, string $message, ?string $path = null): void
    {
        $this->add(new Finding('FAIL', $code, $message, $path));
    }

    public function hasFailures(): bool
    {
        return $this->count('FAIL') > 0;
    }

    public function count(string $level): int
    {
        return count(array_filter(
            $this->findings,
            static fn (Finding $finding): bool => $finding->level === $level,
        ));
    }

    /**
     * @return array{findings:list<array{level:string,code:string,message:string,path:string|null}>,summary:array{pass:int,warn:int,fail:int}}
     */
    public function toArray(): array
    {
        return [
            'findings' => array_map(
                static fn (Finding $finding): array => $finding->toArray(),
                $this->findings,
            ),
            'summary' => [
                'pass' => $this->count('PASS'),
                'warn' => $this->count('WARN'),
                'fail' => $this->count('FAIL'),
            ],
        ];
    }

    public function renderText(): string
    {
        $lines = [];

        foreach ($this->findings as $finding) {
            $location = $finding->path === null ? '' : " [{$finding->path}]";
            $lines[] = "{$finding->level} {$finding->code}{$location}: {$finding->message}";
        }

        $lines[] = sprintf(
            'Summary: PASS %d | WARN %d | FAIL %d',
            $this->count('PASS'),
            $this->count('WARN'),
            $this->count('FAIL'),
        );

        return implode(PHP_EOL, $lines).PHP_EOL;
    }
}
