<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Unit;

use IvanBaric\Corexis\Support\PublicUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PublicUrlTest extends TestCase
{
    #[DataProvider('safeUrls')]
    public function test_it_accepts_safe_public_urls(string $url): void
    {
        $this->assertSame($url, (new PublicUrl)->sanitize($url));
    }

    /** @return iterable<string, array{string}> */
    public static function safeUrls(): iterable
    {
        yield 'https' => ['https://example.com/path?q=1'];
        yield 'relative path' => ['/kontakt'];
        yield 'fragment' => ['#kontakt'];
        yield 'mailto' => ['mailto:info@example.com'];
        yield 'telephone' => ['tel:+385123456'];
    }

    #[DataProvider('unsafeUrls')]
    public function test_it_rejects_unsafe_public_urls(string $url): void
    {
        $this->assertNull((new PublicUrl)->sanitize($url));
    }

    /** @return iterable<string, array{string}> */
    public static function unsafeUrls(): iterable
    {
        yield 'javascript' => ['javascript:alert(1)'];
        yield 'encoded javascript separator' => ['javascript&colon;alert(1)'];
        yield 'data' => ['data:text/html,<script>alert(1)</script>'];
        yield 'protocol relative' => ['//evil.example/path'];
        yield 'backslash normalized external URL' => ['/\\evil.example/path'];
        yield 'control character' => ["https://example.com\nmalicious"];
    }
}
