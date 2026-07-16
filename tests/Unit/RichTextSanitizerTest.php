<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Unit;

use IvanBaric\Corexis\Support\PublicUrl;
use IvanBaric\Corexis\Support\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

final class RichTextSanitizerTest extends TestCase
{
    public function test_it_removes_executable_html_and_preserves_safe_formatting(): void
    {
        $sanitizer = new RichTextSanitizer(new PublicUrl);
        $html = $sanitizer->sanitize(
            '<p onclick="alert(1)"><strong>Siguran tekst</strong>'
            .'<script>alert(2)</script><a href="javascript:alert(3)" target="_blank">Link</a>'
            .'<a href="https://example.com" target="_blank">Siguran link</a></p>',
        );

        $this->assertStringContainsString('<strong>Siguran tekst</strong>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<script', $html);
    }
}
