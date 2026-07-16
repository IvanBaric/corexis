<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use IvanBaric\Corexis\Http\Middleware\SecureResponseHeaders;
use IvanBaric\Corexis\Tests\TestCase;

final class SecureResponseHeadersTest extends TestCase
{
    public function test_web_responses_receive_default_security_headers(): void
    {
        $response = app(SecureResponseHeaders::class)->handle(
            Request::create('/corexis-security-headers'),
            static fn () => response('ok'),
        );

        TestResponse::fromBaseResponse($response)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }
}
