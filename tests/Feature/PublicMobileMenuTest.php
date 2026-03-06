<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PublicMobileMenuTest extends TestCase
{
    public function test_guest_can_see_institutional_links_inside_mobile_menu(): void
    {
        $html = View::make('partials.navbar')->render();

        $this->assertStringContainsString('data-mobile-section="institucional"', $html);
        $this->assertStringContainsString(route('manifesto'), $html);
        $this->assertStringContainsString(route('quem-somos'), $html);
        $this->assertStringContainsString(route('como-funciona'), $html);
        $this->assertStringContainsString(route('valores'), $html);
        $this->assertStringContainsString(route('contato'), $html);
    }
}
