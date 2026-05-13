<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Support;

final class PackageConfig
{
    public static function inherited(string $package, string $context): bool
    {
        return config(sprintf('%s.%s.mode', $package, $context), 'inherit') === 'inherit';
    }

    public static function corexis(string $key, mixed $default = null): mixed
    {
        return config('corexis.'.$key, $default);
    }
}
