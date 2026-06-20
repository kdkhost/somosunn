<?php

namespace Tests\Unit;

use App\Services\CrmScoreService;
use Tests\TestCase;

class CrmScoreServiceTest extends TestCase
{
    public function test_category_is_resolved_by_score(): void
    {
        $service = new CrmScoreService();

        $this->assertSame('Lead Frio', $service->categoryFor(10));
        $this->assertSame('Lead Morno', $service->categoryFor(30));
        $this->assertSame('Lead Quente', $service->categoryFor(80));
        $this->assertSame('Cliente', $service->categoryFor(120));
        $this->assertSame('Embaixador', $service->categoryFor(200));
    }
}
