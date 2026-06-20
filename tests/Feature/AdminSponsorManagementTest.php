<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminSponsorManagementTest extends TestCase
{
    public function test_admin_sponsor_routes_are_registered(): void
    {
        $this->assertNotNull(app('router')->getRoutes()->getByName('admin.sponsors.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('admin.sponsor-plans.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('admin.sponsor-banners.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('admin.companies.index'));
    }
}
