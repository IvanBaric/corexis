<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SecureResponseHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ((array) config('corexis.security_headers.headers', []) as $name => $value) {
            if (! is_string($name) || $name === '' || ! is_string($value) || $value === '') {
                continue;
            }

            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
