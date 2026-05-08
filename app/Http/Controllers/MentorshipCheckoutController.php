<?php

namespace App\Http\Controllers;

use App\Models\Mentorship;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use App\Services\OrderSettlementService;
use App\Services\Payment\MercadoPagoService;
use App\Support\MarketplaceFee;
use App\Traits\SumUpIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MentorshipCheckoutController extends Controller
{
    use SumUpIntegration;
    public function show(Mentorship $mentorship)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faca login para finalizar a compra da mentoria.');
        }

        if ($mentorship->isClosedForPublic()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Esta mentoria ja encerrou.');
        }

        $seller = $mentorship->mentor ?: User::find($mentorship->mentor_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador nao esta habilitado para vender no marketplace.');
        }

        $effectiveTotal = round((float) ($mentorship->effective_price ?? ($mentorship->price ?? 0)), 2);
        if ($effectiveTotal <= 0) {
            $mpEnabled = false;
            $preferredGateway = null;

            return view('checkout.mentorship', compact('mentorship', 'mpEnabled', 'preferredGateway'));
        }

        $sellerId = (int) ($mentorship->mentor_id);
        $platformOwnerId = \App\Models\Setting::get('platform_owner_id', 2);
        $isPlatformOwner = $sellerId === (int) $platformOwnerId;

        $sellerMpAccount = null;
        if (!$isPlatformOwner) {
            $sellerMpAccount = \App\Models\GatewayAccount::resolveForSeller($sellerId);
        } else {
            $sellerMpAccount = \App\Models\GatewayAccount::resolveGlobalSettings();
        }

        $mpEnabled = $sellerMpAccount['mpEnabled'] ?? false;
        $preferredGateway = 'mercadopago';

        if (!$mpEnabled) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Esta mentoria nao esta disponivel para compra: o mentor ainda nao configurou um metodo de pagamento.');
        }

        return view('checkout.mentorship', compact('mentorship', 'mpEnabled', 'preferredGateway'));
    }

    public function process(
        Request $request,
        Mentorship $mentorship,
        MercadoPagoService $mpService,
        CouponService $couponService,
        OrderSettlementService $orderSettlementService
    ) {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', 'Faca login para finalizar a compra da mentoria.');
        }

        if ($mentorship->isClosedForPublic()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Esta mentoria ja encerrou.');
        }

        $seller = $mentorship->mentor ?: User::find($mentorship->mentor_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador nao esta habilitado para vender no marketplace.');
        }

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
            'gateway_provider' => 'nullable|string|in:mercadopago,sumup',
        ]);

        $effectiveTotal = round((float) ($mentorship->effective_price ?? ($mentorship->price ?? 0)), 2);
        $gatewayProvider = 'free';

        if ($effectiveTotal > 0) {
            $chosenGateway = $request->input('gateway_provider', 'mercadopago');

            if ($chosenGateway === 'sumup') {
                if (!$this->shouldShowSumUp($effectiveTotal, 'mentorship', $this->getUserType())) {
                    return back()->with('error', 'SumUp não disponível para esta mentoria.');
                }
                $gatewayProvider = 'sumup';
            } else {
                $sellerId = (int) ($mentorship->mentor_id);
                $platformOwnerId = \App\Models\Setting::get('platform_owner_id', 2);
                $isPlatformOwner = $sellerId === (int) $platformOwnerId;

                $sellerMpAccount = null;
                if (!$isPlatformOwner) {
                    $sellerMpAccount = \App\Models\GatewayAccount::resolveForSeller($sellerId);
                } else {
                    $sellerMpAccount = \App\Models\GatewayAccount::resolveGlobalSettings();
                }

                $gatewayProvider = 'mercadopago';

                if (!($sellerMpAccount['mpEnabled'] ?? false)) {
                    // Fallback para SumUp se disponível
                    if ($this->shouldShowSumUp($effectiveTotal, 'mentorship', $this->getUserType())) {
                        $gatewayProvider = 'sumup';
                    } else {
                        return back()->with('error', 'Metodo de pagamento nao disponivel para esta mentoria.');
                    }
                }
            }
        } else {
            $existingFreeOrder = $this->findExistingFreeOrder((int) Auth::id(), 'mentorship', (int) $mentorship->id);
            if ($existingFreeOrder) {
                return redirect()
                    ->route('mentorships.show', $mentorship)
                    ->with('success', 'Mentoria liberada com sucesso.');
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
                    'gateway' => $finalTotal <= 0 ? 'free' : $gatewayProvider,
                    'gateway_account_id' => null,
                    'metadata' => [
                        'context' => 'mentorship',
                        'sale_type' => 'mentorship',
                        'public_token' => Str::random(40),
                        'original_total_amount' => $originalTotal,
                        'regular_total_amount' => $regularUnitPrice,
                        'platform_fee_percent' => $platformFeePercent,
                        'is_free_checkout' => $finalTotal <= 0,
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
            $msg = collect($e->errors())->flatten()->first() ?? 'Nao foi possivel aplicar o cupom.';
            return back()->with('error', $msg)->withInput();
        }

        if ((float) ($order->total_amount ?? 0) <= 0) {
            try {
                $orderSettlementService->settleAsPaid($order, [
                    'transaction_id' => 'FREE-MENTORSHIP-' . $order->id . '-' . now()->format('YmdHis'),
                    'payment_method' => 'free_checkout',
                    'queue_invoice_email' => false,
                    'send_notifications' => false,
                    'gateway_data' => [
                        'source' => 'free_mentorship_checkout',
                        'automatic' => true,
                    ],
                ]);
            } catch (\Throwable $e) {
                return back()->with('error', 'Erro ao liberar mentoria gratuita: ' . $e->getMessage());
            }

            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('success', 'Mentoria liberada com sucesso.');
        }

            $order->load('items', 'user');

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

            $sellerId = (int) ($mentorship->mentor_id);
            $platformOwnerId = \App\Models\Setting::get('platform_owner_id', 2);
            $isPlatformOwner = $sellerId === (int) $platformOwnerId;
            
            $sellerMpAccount = null;
            if (!$isPlatformOwner) {
                $sellerMpAccount = \App\Models\GatewayAccount::resolveForSeller($sellerId);
            }

            $mpPublicKey = (!$isPlatformOwner && is_array($sellerMpAccount)) ? ($sellerMpAccount['mpPublicKey'] ?? null) : null;

            return view('checkout.transparent', [
                'order' => $order,
                'preferenceId' => $preference['id'] ?? '',
                'publicKey' => $mpPublicKey ?: config('payments.mercadopago.public_key') ?: \App\Models\Setting::get('mp_public_key'),
            ]);
    }

    private function findExistingFreeOrder(int $userId, string $itemType, int $itemId): ?Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('total_amount', '<=', 0)
            ->whereHas('items', function ($query) use ($itemType, $itemId) {
                $query->where('item_type', $itemType)
                    ->where('item_id', $itemId);
            })
            ->latest('id')
            ->first();
    }
}
