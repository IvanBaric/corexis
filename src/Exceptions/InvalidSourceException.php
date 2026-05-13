<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Exceptions;

class InvalidSourceException extends CorexisException
{
    public static function forSource(string $source): self
    {
        return new self(sprintf('The source [%s] is not allowed by Corexis.', $source));
    }
}
