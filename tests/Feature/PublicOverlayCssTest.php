<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class PublicOverlayCssTest extends TestCase
{
    public function test_public_overlay_css_keeps_the_page_stable_without_breaking_flux_flyouts(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/public-overlays.css');

        $this->assertIsString($css);
        $this->assertStringContainsString('body[data-corexis-public-shell]', $css);
        $this->assertStringContainsString('scrollbar-gutter: stable', $css);
        $this->assertStringContainsString('ui-dropdown[data-open]', $css);
        $this->assertStringContainsString('overflow-y: scroll !important', $css);
        $this->assertStringContainsString(':not(:has(> body[data-corexis-public-shell] [data-flux-modal] > dialog[open]))', $css);
        $this->assertStringContainsString('dialog[open][data-flux-flyout]', $css);
        $this->assertStringContainsString('scrollbar-gutter: auto', $css);
    }
}
