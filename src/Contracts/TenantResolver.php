<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Contracts;

interface TenantResolver
{
    public function enabled(): bool;

    public function current(): mixed;

    public function id(): int|string|null;

    public function uuid(): ?string;

    public function type(): ?string;
}
