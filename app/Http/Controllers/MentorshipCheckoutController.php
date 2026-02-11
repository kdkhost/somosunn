<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use App\Services\Payment\MercadoPagoService;
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
            return redirect()->route('login')->with('error', 'Faça login para finalizar a compra da mentoria.');
        }

        $seller = $mentorship->mentor ?: User::find($mentorship->mentor_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador não está habilitado para vender no marketplace.');
        }

        $gateway = GatewayAccount::where('user_id', (int) $mentorship->mentor_id)
            ->where('enabled', true)
            ->first();

        if (!$gateway) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador ainda não configurou o recebimento de pagamentos.');
        }

        return view('checkout.mentorship', compact('mentorship', 'gateway'));
    }

    public function process(Request $request, Mentorship $mentorship, MercadoPagoService $mpService, CouponService $couponService)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Faça login para finalizar a compra da mentoria.');
        }

        $seller = $mentorship->mentor ?: User::find($mentorship->mentor_id);
        if (!$seller || !$seller->canSellOnMarketplace()) {
            return redirect()
                ->route('mentorships.show', $mentorship)
                ->with('error', 'Este criador não está habilitado para vender no marketplace.');
        }

        $gateway = GatewayAccount::where('user_id', (int) $mentorship->mentor_id)
            ->where('enabled', true)
            ->firstOrFail();

        $request->validate([
            'coupon_code' => 'nullable|string|max:40',
        ]);

        $order = null;
        $couponCode = $couponService->normalizeCode($request->input('coupon_code'));

        try {
            DB::transaction(function () use ($mentorship, $gateway, $couponCode, $couponService, &$order) {
                $originalTotal = round((float) $mentorship->price, 2);

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

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'seller_id' => (int) $mentorship->mentor_id,
                    'status' => 'pending',
                    'total_amount' => $finalTotal,
                    'fee_amount' => 0,
                    'platform_fee_amount' => 0,
                    'currency' => 'BRL',
                    'gateway' => $gateway->provider,
                    'gateway_account_id' => $gateway->id,
                    'metadata' => [
                        // Reusa os back_urls do checkout padrão.
                        'context' => 'course',
                        'marketplace_context' => 'mentorship',
                        'public_token' => Str::random(40),
                        'original_total_amount' => $originalTotal,
                    ],
                ]);

                $order->items()->create([
                    'item_type' => 'mentorship',
                    'item_id' => $mentorship->id,
                    'title' => $mentorship->title,
                    'price' => $finalTotal,
                    'quantity' => 1,
                    'data' => [
                        'original_unit_price' => (float) $mentorship->price,
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
            if ($gateway->provider === 'mercadopago') {
                $preference = $mpService->createPreference($order, $gateway);

                return view('checkout.transparent', [
                    'order' => $order,
                    'preferenceId' => $preference['id'],
                    'publicKey' => $gateway->public_key,
                ]);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }

        return back()->with('error', 'Gateway não suportado.');
    }
}
