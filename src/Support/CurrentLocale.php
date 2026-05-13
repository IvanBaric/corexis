<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use IvanBaric\Corexis\Contracts\LocaleResolver;

final readonly class CurrentLocale
{
    public function __construct(
        private LocaleResolver $resolver,
    ) {}

    public function enabled(): bool
    {
        return $this->resolver->enabled();
    }

    public function current(): ?string
    {
        return $this->resolver->current();
    }

    public function default(): ?string
    {
        return $this->resolver->default();
    }

    public function fallback(): ?string
    {
        return $this->resolver->fallback();
    }

    public function available(): array
    {
        return $this->resolver->available();
    }
}
