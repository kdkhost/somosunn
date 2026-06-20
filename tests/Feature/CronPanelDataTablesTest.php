<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\CronController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CronPanelDataTablesTest extends TestCase
{
    public function test_admin_cron_data_route_is_available_for_server_side_pagination(): void
    {
        $route = Route::getRoutes()->getByName('admin.cron.data');

        $this->assertNotNull($route);
        $this->assertSame('admin/cron/data', $route->uri());
        $this->assertSame(CronController::class . '@data', $route->getActionName());
        $this->assertContains('auth', $route->gatherMiddleware());
        $this->assertContains('admin', $route->gatherMiddleware());
    }

    public function test_cron_panel_uses_local_datatables_and_full_page_navigation(): void
    {
        $view = file_get_contents(resource_path('views/admin/cron/index.blade.php'));
        $sidebar = file_get_contents(resource_path('views/admin/partials/sidebar.blade.php'));
        $layout = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        $this->assertStringContainsString("asset('assets/admin/datatables/jquery.dataTables.min.js')", $view);
        $this->assertStringContainsString("asset('assets/admin/datatables/pt-BR.json')", $view);
        $this->assertStringNotContainsString('cdn.datatables.net', $view);
        $this->assertStringContainsString('data-no-pjax="true"', $sidebar);
        $this->assertStringContainsString("'/admin/cron'", $layout);
    }
}
