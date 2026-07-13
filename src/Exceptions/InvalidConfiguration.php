<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Exceptions;

use LogicException;

final class InvalidConfiguration extends LogicException
{
    public static function invalidClass(string $key, mixed $value, string $expectedType): self
    {
        return new self(sprintf(
            'Invalid configuration value for [%s]. Expected an instantiable class-string of type [%s].',
            $key,
            $expectedType,
        ));
    }

    public static function invalidTable(string $key, mixed $value): self
    {
        return new self(sprintf(
            'Invalid configuration value for [%s]. Expected a valid database table name.',
            $key,
        ));
    }
}
