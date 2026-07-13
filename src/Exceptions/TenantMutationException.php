<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Exceptions;

final class TenantMutationException extends CorexisException
{
    public static function make(string $column): self
    {
        return new self(sprintf(
            'The tenant column [%s] cannot be changed through a regular model update.',
            $column,
        ));
    }
}
