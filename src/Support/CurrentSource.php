<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use IvanBaric\Corexis\Contracts\SourceResolver;

final readonly class CurrentSource
{
    public function __construct(
        private SourceResolver $resolver,
    ) {}

    public function current(): string
    {
        return $this->resolver->current();
    }

    public function allowed(): array
    {
        return $this->resolver->allowed();
    }

    public function isAllowed(string $source): bool
    {
        return $this->resolver->isAllowed($source);
    }
}
