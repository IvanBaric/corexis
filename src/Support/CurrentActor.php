<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

use IvanBaric\Corexis\Contracts\ActorResolver;

final readonly class CurrentActor
{
    public function __construct(
        private ActorResolver $resolver,
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

    public function type(): ?string
    {
        return $this->resolver->type();
    }
}
