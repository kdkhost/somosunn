<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\BusinessMatchService;
use App\Services\MemberSuggestionService;
use Tests\TestCase;

class BusinessMatchServiceTest extends TestCase
{
    public function test_business_match_score_uses_profile_similarity(): void
    {
        $service = new BusinessMatchService(new MemberSuggestionService());

        $user = new User(['city' => 'Rio de Janeiro', 'state' => 'RJ', 'segment' => 'Tecnologia', 'interests' => 'networking, vendas']);
        $matched = new User(['city' => 'Rio de Janeiro', 'state' => 'RJ', 'segment' => 'Tecnologia', 'interests' => 'networking, growth']);

        $this->assertGreaterThan(0, $service->calculateScore($user, $matched));
    }
}
