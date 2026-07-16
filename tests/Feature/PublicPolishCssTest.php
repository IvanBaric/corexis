<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class PublicPolishCssTest extends TestCase
{
    public function test_public_polish_css_exposes_keyboard_navigation_helpers(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/public-polish.css');

        $this->assertIsString($css);
        $this->assertStringContainsString('.cx-public-skip-link', $css);
        $this->assertStringContainsString('focus-visible:translate-y-0', $css);
        $this->assertStringContainsString('#main-content:focus', $css);
    }
}
