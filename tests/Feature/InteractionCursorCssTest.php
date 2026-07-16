<?php

declare(strict_types=1);

namespace IvanBaric\Corexis\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class InteractionCursorCssTest extends TestCase
{
    public function test_shared_cursor_css_covers_flux_and_javascript_interactions(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/interaction-cursors.css');

        $this->assertIsString($css);
        $this->assertStringContainsString("[role='tab']", $css);
        $this->assertStringContainsString('[data-flux-tab]', $css);
        $this->assertStringContainsString('[data-flux-menu-item]', $css);
        $this->assertStringContainsString('[data-flux-menu-radio]', $css);
        $this->assertStringContainsString('[data-flux-menu-checkbox]', $css);
        $this->assertStringContainsString('[data-flux-checkbox-cards]', $css);
        $this->assertStringContainsString('[data-flux-radio-segmented]', $css);
        $this->assertStringContainsString('[data-flux-switch]', $css);
        $this->assertStringContainsString('[data-flux-table-sortable]', $css);
        $this->assertStringContainsString('[\\@click]', $css);
        $this->assertStringContainsString('[wire\\:click]', $css);
        $this->assertStringContainsString('[x-on\\:click]', $css);
        $this->assertStringContainsString('cursor: pointer', $css);
        $this->assertStringContainsString('cursor: not-allowed', $css);
    }
}
