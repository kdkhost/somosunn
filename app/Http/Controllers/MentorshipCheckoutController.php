<?php

namespace App\Http\Controllers;

use App\Models\Mentorship;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use App\Services\Payment\MercadoPagoService;
use App\Support\MarketplaceFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MentorshipCheckoutController extends Controller
{
    public function show(Mentorship $mentorship)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faça login para finalizar a compra da mentoria.');
        }

        $seller = $mentorship->mentor ?: User::find($mentorship->mentor_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador não está habilitado para vender no marketplace.');
        }

        // Verificar gateways configurados pelo vendedor (tabela gateway_accounts)
        $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $seller->id);
        $mpEnabled        = $gateways['mpEnabled'];
        $psEnabled        = $gateways['psEnabled'];
        $preferredGateway = $gateways['preferredGateway'];

        if (!$mpEnabled && !$psEnabled) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Esta mentoria não está disponível para compra: o mentor ainda não configurou um método de pagamento.');
        }

        return view('checkout.mentorship', compact('mentorship', 'mpEnabled', 'psEnabled', 'preferredGateway'));
    }

    public function process(Request $request, Mentorship $mentorship, MercadoPagoService $mpService, \App\Services\Payment\PagSeguroService $psService, CouponService $couponService)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faça login para finalizar a compra da mentoria.');
        }

        $seller = $mentorship->mentor ?: User::find($mentorship->mentor_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador não está habilitado para vender no marketplace.');
        }

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
            'gateway_provider' => 'nullable|string|in:mercadopago,pagseguro',
        ]);

        $gatewayProvider = $request->input('gateway_provider', 'mercadopago');

        // Validar que o vendedor/mentor tem o gateway selecionado configurado
        $gateways = \App\Models\GatewayAccount::resolveForSeller((int) $mentorship->mentor_id);
        if (($gatewayProvider === 'mercadopago' && !$gateways['mpEnabled'])
            || ($gatewayProvider === 'pagseguro' && !$gateways['psEnabled'])) {
            if ($gateways['mpEnabled']) {
                $gatewayProvider = 'mercadopago';
            } elseif ($gateways['psEnabled']) {
                $gatewayProvider = 'pagseguro';
            } else {
                return back()->with('error', 'Método de pagamento não disponível para esta mentoria. O mentor ainda não configurou um gateway.');
            }
        }

        $order = null;
        $couponCode = $couponService->normalizeCode($request->input('coupon_code'));

        try {
            DB::transaction(function () use ($mentorship, $couponCode, $couponService, &$order, $gatewayProvider) {
                $regularUnitPrice = round((float) ($mentorship->price ?? 0), 2);
                $effectiveUnitPrice = round((float) ($mentorship->effective_price ?? $regularUnitPrice), 2);
                $originalTotal = $effectiveUnitPrice;

                $discountAmount = 0.0;
                $coupon = null;

                if ($couponCode !== '') {
                    $result = $couponService->validateAndCalculateLocked(
                        $couponCode,
                        CouponService::CONTEXT_MENTORSHIP,
                        (int) $mentorship->id,
                        (int) Auth::id(),
                        (float) $originalTotal
                    );
                    $coupon = $result['coupon'];
                    $discountAmount = (float) $result['discount_amount'];
                }

                $finalTotal = max(0, round($originalTotal - $discountAmount, 2));
                $platformFeePercent = MarketplaceFee::percent();
                $platformFeeAmount = MarketplaceFee::amount($finalTotal);

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => (int) $mentorship->mentor_id,
                    'status' => 'pending',
                    'total_amount' => $finalTotal,
                    'fee_amount' => 0,
                    'platform_fee_amount' => $platformFeeAmount,
                    'currency' => 'BRL',
                    'gateway' => $gatewayProvider,
                    'gateway_account_id' => null,
                    'metadata' => [
                        'context' => 'mentorship',
                        'sale_type' => 'mentorship',
                        'public_token' => Str::random(40),
                        'original_total_amount' => $originalTotal,
                        'regular_total_amount' => $regularUnitPrice,
                        'platform_fee_percent' => $platformFeePercent,
                    ],
                ]);

                $order->items()->create([
                    'item_type' => 'mentorship',
                    'item_id' => $mentorship->id,
                    'title' => $mentorship->title,
                    'price' => $finalTotal,
                    'quantity' => 1,
                    'data' => [
                        'original_unit_price' => $effectiveUnitPrice,
                        'regular_unit_price' => $regularUnitPrice,
                        'flash_sale_price' => $mentorship->flash_sale_price !== null ? (float) $mentorship->flash_sale_price : null,
                        'flash_sale_ends_at' => $mentorship->flash_sale_ends_at ? $mentorship->flash_sale_ends_at->toIso8601String() : null,
                        'discount_amount' => $discountAmount,
                    ],
                ]);

                if ($coupon && $discountAmount > 0) {
                    $order->update([
                        'metadata' => array_merge($order->metadata ?? [], [
                            'coupon' => [
                                'id' => $coupon->id,
                                'code' => $coupon->code,
                                'discount_type' => $coupon->discount_type,
                                'discount_value' => (float) $coupon->discount_value,
                                'discount_amount' => $discountAmount,
                            ],
                        ]),
                    ]);

                    $couponService->reserveRedemption($coupon, (int) Auth::id(), (int) $order->id, $discountAmount);
                }
            });
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?? 'Não foi possível aplicar o cupom.';
            return back()->with('error', $msg)->withInput();
        }

        try {
            $order->load('items', 'user');

            if ($gatewayProvider === 'pagseguro') {
                return view('checkout.pagseguro_transparent', [
                    'order' => $order,
                    'publicKey' => config('payments.pagseguro.public_key'),
                    'pixAvailable' => $psService->isPixAvailable($order),
                ]);
            } else {
                $preference = $mpService->createPreference($order, [
                    'statement_descriptor' => 'UNN MENTORIAS',
                ]);

                $order->update([
                    'metadata' => array_merge($order->metadata ?? [], [
                        'mercadopago_preference_id' => $preference['id'] ?? null,
                        'mercadopago_init_point' => $preference['init_point'] ?? null,
                        'mercadopago_sandbox_init_point' => $preference['sandbox_init_point'] ?? null,
                    ]),
                ]);

                return view('checkout.transparent', [
                    'order' => $order,
                    'preferenceId' => $preference['id'] ?? '',
                    'publicKey' => config('payments.mercadopago.public_key'),
                ]);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }
}
