<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckFeature;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

class CheckFeatureRedirectTest extends TestCase
{
    public function test_feature_denial_redirects_to_planos_route(): void
    {
        $request = Request::create('/chat/start/11', 'GET');
        $request->setUserResolver(function () {
            return new class {
                public int $id = 77;

                public function canAccessFeature($feature): bool
                {
                    return false;
                }

                public function isAdmin(): bool
                {
                    return false;
                }
            };
        });

        $response = app(CheckFeature::class)->handle($request, fn() => response('ok'), 'chat');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(route('planos', ['feature' => 'chat']), $response->getTargetUrl());
        $this->assertSame(
            'Seu plano atual nao inclui este recurso. Veja os planos recomendados para liberar o acesso.',
            session('warning')
        );
    }
}
