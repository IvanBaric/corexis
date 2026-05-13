<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use IvanBaric\Corexis\Contracts\TenantResolver;

final readonly class CurrentTenant
{
    public function __construct(
        private TenantResolver $resolver,
    ) {}

    public function enabled(): bool
    {
        return $this->resolver->enabled();
    }

    public function current(): mixed
    {
        return $this->resolver->current();
    }

    public function id(): int|string|null
    {
        return $this->resolver->id();
    }

    public function uuid(): ?string
    {
        return $this->resolver->uuid();
    }

    public function type(): ?string
    {
        return $this->resolver->type();
    }
}
