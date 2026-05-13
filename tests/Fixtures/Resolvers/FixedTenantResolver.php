<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Fixtures\Resolvers;

use IvanBaric\Corexis\Contracts\TenantResolver;

final class FixedTenantResolver implements TenantResolver
{
    public static int|string|null $id = 123;

    public function enabled(): bool
    {
        return true;
    }

    public function current(): mixed
    {
        return ['id' => self::$id];
    }

    public function id(): int|string|null
    {
        return self::$id;
    }

    public function uuid(): ?string
    {
        return 'tenant-uuid';
    }

    public function type(): ?string
    {
        return 'test';
    }
}
