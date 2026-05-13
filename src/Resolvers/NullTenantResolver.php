<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Resolvers;

use IvanBaric\Corexis\Contracts\TenantResolver;

final class NullTenantResolver implements TenantResolver
{
    public function enabled(): bool
    {
        return false;
    }

    public function current(): mixed
    {
        return null;
    }

    public function id(): int|string|null
    {
        return null;
    }

    public function uuid(): ?string
    {
        return null;
    }

    public function type(): ?string
    {
        return null;
    }
}
