<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Resolvers;

use IvanBaric\Corexis\Contracts\SourceResolver;
use IvanBaric\Corexis\Exceptions\InvalidSourceException;

final class RequestSourceResolver implements SourceResolver
{
    public function current(): string
    {
        if (app()->runningInConsole()) {
            return $this->validated('console');
        }

        $request = request();

        if ($this->isApiRequest($request)) {
            return $this->validated('api');
        }

        return $this->validated((string) config('corexis.source.default', 'system'));
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

    private function isApiRequest(mixed $request): bool
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return true;
        }

        $route = $request->route();

        if ($route === null || ! method_exists($route, 'gatherMiddleware')) {
            return false;
        }

        return in_array('api', $route->gatherMiddleware(), true);
    }

    private function validated(string $source): string
    {
        if (! $this->isAllowed($source)) {
            throw InvalidSourceException::forSource($source);
        }

        return $source;
    }
}
