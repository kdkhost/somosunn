<?php

namespace Tests\Feature;

use Tests\TestCase;

class CompanyPublicProfileTest extends TestCase
{
    public function test_public_company_route_exists(): void
    {
        $this->assertTrue(route('companies.show', ['slug' => 'empresa-teste']) !== '');
    }
}
