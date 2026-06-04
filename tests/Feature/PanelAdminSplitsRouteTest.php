<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PanelAdminSplitsRouteTest extends TestCase
{
    public function test_new_admin_panel_exposes_global_split_accounting_routes(): void
    {
        $this->assertTrue(Route::has('panel.admin.splits.index'));
        $this->assertTrue(Route::has('panel.admin.splits.pay'));
        $this->assertSame('/painel/admin/splits', route('panel.admin.splits.index', absolute: false));
    }

    public function test_new_admin_sidebar_contains_global_split_accounting_link(): void
    {
        $sidebar = file_get_contents(resource_path('views/panel/partials/sidebar.blade.php'));

        $this->assertStringContainsString("route('panel.admin.splits.index')", $sidebar);
        $this->assertStringContainsString('Contabilidade de Rateios', $sidebar);
    }
}
