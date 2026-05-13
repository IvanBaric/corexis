<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Exceptions;

class TenantNotResolvedException extends CorexisException
{
    public static function make(): self
    {
        return new self('Corexis tenant context is enabled, but no tenant id could be resolved.');
    }
}
