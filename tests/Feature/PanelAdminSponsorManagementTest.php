<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelAdminSponsorManagementTest extends TestCase
{
    public function test_panel_admin_sponsor_routes_are_registered(): void
    {
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.admin.sponsors.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.admin.sponsor-plans.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.admin.sponsor-banners.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.admin.companies.index'));
    }
}
