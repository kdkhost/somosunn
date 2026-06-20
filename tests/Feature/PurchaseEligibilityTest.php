<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsurePurchaseEligibility;
use App\Models\User;
use App\Services\LegalConsentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class PurchaseEligibilityTest extends TestCase
{
    public function test_guest_cannot_process_purchase(): void
    {
        $consent = Mockery::mock(LegalConsentService::class);
        $request = $this->requestWithSession();

        $response = (new EnsurePurchaseEligibility($consent))->handle(
            $request,
            fn () => response('não deveria executar'),
        );

        $this->assertTrue($response->isRedirect(route('login')));
    }

    public function test_unverified_user_cannot_process_purchase(): void
    {
        $consent = Mockery::mock(LegalConsentService::class);
        $request = $this->requestWithSession(new User([
            'email' => 'cliente@example.com',
            'email_verified_at' => null,
        ]));

        $response = (new EnsurePurchaseEligibility($consent))->handle(
            $request,
            fn () => response('não deveria executar'),
        );

        $this->assertTrue($response->isRedirect(route('verification.notice')));
    }

    public function test_verified_user_without_current_consent_cannot_process_purchase(): void
    {
        $user = new User(['email' => 'cliente@example.com']);
        $user->email_verified_at = now();

        $consent = Mockery::mock(LegalConsentService::class);
        $consent->shouldReceive('hasAcceptedCurrentVersion')->once()->with($user)->andReturnFalse();

        $response = (new EnsurePurchaseEligibility($consent))->handle(
            $this->requestWithSession($user),
            fn () => response('não deveria executar'),
        );

        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_verified_user_with_current_consent_can_process_purchase(): void
    {
        $user = new User(['email' => 'cliente@example.com']);
        $user->email_verified_at = now();

        $consent = Mockery::mock(LegalConsentService::class);
        $consent->shouldReceive('hasAcceptedCurrentVersion')->once()->with($user)->andReturnTrue();

        $response = (new EnsurePurchaseEligibility($consent))->handle(
            $this->requestWithSession($user),
            fn () => response('compra liberada'),
        );

        $this->assertSame('compra liberada', $response->getContent());
    }

    public function test_all_purchase_processing_routes_use_eligibility_middleware(): void
    {
        $routeNames = [
            'subscription.process',
            'subscription.checkout',
            'subscription.prepare-sumup',
            'events.reserve',
            'events.checkout',
            'events.exhibitor.checkout',
            'events.exhibitor.show',
            'events.payment.process-gateway',
            'mentorships.checkout.process',
            'mentorships.checkout.show',
            'seller-products.checkout.process',
            'seller-products.checkout.show',
            'checkout.process_payment',
            'checkout.sumup.pix',
            'checkout.sumup.recreate',
            'checkout.process',
            'checkout.show',
            'api.sumup.checkout',
        ];

        foreach ($routeNames as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Rota {$routeName} não encontrada.");
            $this->assertContains('purchase.eligible', $route->gatherMiddleware(), "Rota {$routeName} sem proteção de compra.");
        }
    }

    private function requestWithSession(?User $user = null): Request
    {
        $request = Request::create('/comprar', 'POST');
        $request->setLaravelSession(app('session')->driver());
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
