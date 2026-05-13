<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Contracts;

interface ActorResolver
{
    public function enabled(): bool;

    public function current(): mixed;

    public function id(): int|string|null;

    public function type(): ?string;
}
