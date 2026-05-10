<?php

namespace App\Http\Controllers;

use App\Models\PointsLog;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\Setting;
use App\Notifications\RedemptionRequestedForProvider;
use App\Services\PointsExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class RedemptionItemController extends Controller
{
    public function __construct(private readonly PointsExchangeService $exchangeService)
    {
    }

    public function index()
    {
        $items = RedeemableItem::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('panel.redemptions.shop', [
            'items' => $items,
            'exchangeSettings' => $this->exchangeService->settings(),
        ]);
    }

    public function history()
    {
        $redemptions = Redemption::with(['item', 'providerUser'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('panel.redemptions.history', [
            'redemptions' => $redemptions,
            'exchangeSettings' => $this->exchangeService->settings(),
        ]);
    }

    public function redeem(Request $request, RedeemableItem $item)
    {
        if (!$item->is_active) {
            return back()->with('error', 'Este item nao esta mais disponivel.');
        }

        if ((int) $item->stock === 0) {
            return back()->with('error', 'Item fora de estoque.');
        }

        $userId = (int) Auth::id();
        try {
            $redemption = DB::transaction(function () use ($userId, $item) {
                $user = Auth::user()->newQuery()->lockForUpdate()->findOrFail($userId);
                $freshItem = RedeemableItem::query()->lockForUpdate()->findOrFail($item->id);

                if (!$freshItem->is_active) {
                    throw new \RuntimeException('Este item nao esta mais disponivel.');
                }

                if ((int) $freshItem->stock === 0) {
                    throw new \RuntimeException('Item fora de estoque.');
                }

                if ((int) $user->points < (int) $freshItem->points_cost) {
                    throw new \RuntimeException('Voce nao tem UNNBIT suficientes.');
                }

                $user->decrement('points', $freshItem->points_cost);

                if ((int) $freshItem->stock > 0) {
                    $freshItem->decrement('stock');
                }

                $redemptionData = [
                    'user_id' => $user->id,
                    'redeemable_item_id' => $freshItem->id,
                    'provider_type' => $freshItem->provider_type ?: 'platform',
                    'provider_user_id' => $freshItem->provider_user_id,
                    'provider_name' => $freshItem->provider_label,
                    'points_spent' => $freshItem->points_cost,
                    'reference_value' => $freshItem->reference_value,
                    'status' => 'pending',
                    'estimated_delivery_at' => now()->addDays(max(1, (int) ($freshItem->delivery_lead_days ?? 7))),
                ];

                if (Schema::hasColumn('redemptions', 'item_type')) {
                    $redemptionData['item_type'] = $freshItem->item_type ?: 'service';
                }

                if (Schema::hasColumn('redemptions', 'fulfillment_instructions')) {
                    $redemptionData['fulfillment_instructions'] = $freshItem->fulfillment_instructions;
                }

                $redemption = Redemption::create($redemptionData);

                PointsLog::create([
                    'user_id' => $user->id,
                    'action_key' => 'redemption_requested',
                    'points' => -$freshItem->points_cost,
                    'meta' => json_encode([
                        'redemption_id' => $redemption->id,
                        'item_id' => $freshItem->id,
                        'item_name' => $freshItem->name,
                        'provider_name' => $freshItem->provider_label,
                        'reference_value' => $freshItem->reference_value,
                        'coin_name' => $this->exchangeService->settings()['coin_name'],
                        'consumed_forever' => true,
                    ]),
                ]);

                return $redemption->loadMissing(['item', 'providerUser', 'user']);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->notifyProvider($redemption);

        return redirect()
            ->route('panel.redemptions.shop')
            ->with('success', 'Resgate solicitado com sucesso. Os UNNBIT foram consumidos e nao podem ser reutilizados.');
    }

    private function notifyProvider(Redemption $redemption): void
    {
        $provider = $redemption->providerUser;

        if ($provider && trim((string) $provider->email) !== '') {
            $provider->notify(new RedemptionRequestedForProvider($redemption));

            return;
        }

        if ($redemption->provider_type !== 'platform') {
            return;
        }

        $platformEmail = trim((string) (Setting::get('company_email')
            ?: Setting::get('smtp_from_email')
            ?: config('mail.from.address', '')));

        if ($platformEmail === '') {
            return;
        }

        Notification::route('mail', $platformEmail)
            ->notify(new RedemptionRequestedForProvider($redemption));
    }
}
