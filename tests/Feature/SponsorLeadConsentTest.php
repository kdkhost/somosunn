<?php

namespace Tests\Feature;

use App\Http\Requests\Panel\SponsorLeadConsentRequest;
use Tests\TestCase;

class SponsorLeadConsentTest extends TestCase
{
    public function test_sponsor_lead_request_requires_consent(): void
    {
        $request = new SponsorLeadConsentRequest();
        $this->assertArrayHasKey('consent', $request->rules());
    }
}
