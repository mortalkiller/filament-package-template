<?php

declare(strict_types=1);

namespace MortalKiller\PackageStandard;

final readonly class Finding
{
    public function __construct(
        public string $level,
        public string $code,
        public string $message,
        public ?string $path = null,
    ) {}

    /**
     * @return array{level:string,code:string,message:string,path:string|null}
     */
    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'code' => $this->code,
            'message' => $this->message,
            'path' => $this->path,
        ];
    }
}
