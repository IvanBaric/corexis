<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Fixtures\Resolvers;

use IvanBaric\Corexis\Contracts\TenantResolver;

final class UnresolvedTenantResolver implements TenantResolver
{
    public function enabled(): bool
    {
        return true;
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
