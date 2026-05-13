<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Resolvers;

use IvanBaric\Corexis\Contracts\LocaleResolver;

final class AppLocaleResolver implements LocaleResolver
{
    public function enabled(): bool
    {
        return (bool) config('corexis.locale.enabled', false);
    }

    public function current(): ?string
    {
        return app()->getLocale();
    }

    public function default(): ?string
    {
        return config('corexis.locale.default');
    }

    public function fallback(): ?string
    {
        return config('corexis.locale.fallback');
    }

    public function available(): array
    {
        $available = config('corexis.locale.available');

        if (is_array($available)) {
            return array_values(array_filter($available, static fn (mixed $locale): bool => is_string($locale) && $locale !== ''));
        }

        return array_values(array_unique(array_filter([
            $this->current(),
            $this->default(),
            $this->fallback(),
        ], static fn (?string $locale): bool => $locale !== null && $locale !== '')));
    }
}
