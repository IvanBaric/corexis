<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Resolvers;

use IvanBaric\Corexis\Contracts\SourceResolver;
use IvanBaric\Corexis\Exceptions\InvalidSourceException;

final class StaticSourceResolver implements SourceResolver
{
    private static ?string $source = null;

    public static function use(?string $source): void
    {
        self::$source = $source;
    }

    public static function reset(): void
    {
        self::$source = null;
    }

    public function current(): string
    {
        $source = self::$source ?? (string) config('corexis.source.default', 'system');

        if (! $this->isAllowed($source)) {
            throw InvalidSourceException::forSource($source);
        }

        return $source;
    }

    public function allowed(): array
    {
        $allowed = config('corexis.source.allowed', []);

        if (! is_array($allowed)) {
            return [];
        }

        return array_values(array_filter($allowed, static fn (mixed $source): bool => is_string($source) && $source !== ''));
    }

    public function isAllowed(string $source): bool
    {
        return in_array($source, $this->allowed(), true);
    }
}
