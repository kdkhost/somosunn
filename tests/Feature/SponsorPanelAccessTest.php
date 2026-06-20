<?php

namespace Tests\Feature;

use Tests\TestCase;

class SponsorPanelAccessTest extends TestCase
{
    public function test_sponsor_panel_routes_are_registered(): void
    {
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.sponsor.dashboard'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.sponsor.leads.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.sponsor.finance.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.sponsor.campaigns.index'));
        $this->assertNotNull(app('router')->getRoutes()->getByName('panel.sponsor.reports.index'));
    }
}
