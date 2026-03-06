<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedemptionItemController extends Controller
{
    public function index()
    {
        $items = RedeemableItem::query()
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return view('panel.redemptions.shop', compact('items'));
    }

    public function history()
    {
        $redemptions = Redemption::with('item')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('panel.redemptions.history', compact('redemptions'));
    }

    public function redeem(Request $request, RedeemableItem $item)
    {
        if (!$item->is_active) {
            return back()->with('error', 'Este item não está mais disponível.');
        }

        if ((int) $item->stock === 0) {
            return back()->with('error', 'Item fora de estoque.');
        }

        $user = Auth::user();

        if ($user->points < $item->points_cost) {
            return back()->with('error', 'Você não tem pontos suficientes.');
        }

        $user->decrement('points', $item->points_cost);

        if ((int) $item->stock > 0) {
            $item->decrement('stock');
        }

        $redemption = Redemption::create([
            'user_id' => $user->id,
            'redeemable_item_id' => $item->id,
            'provider_type' => $item->provider_type ?: 'platform',
            'provider_user_id' => $item->provider_user_id,
            'provider_name' => $item->provider_label,
            'points_spent' => $item->points_cost,
            'reference_value' => $item->reference_value,
            'status' => 'pending',
            'estimated_delivery_at' => now()->addDays(max(1, (int) ($item->delivery_lead_days ?? 7))),
        ]);

        PointsLog::create([
            'user_id' => $user->id,
            'action_key' => 'redemption_requested',
            'points' => -$item->points_cost,
            'meta' => json_encode([
                'redemption_id' => $redemption->id,
                'item_id' => $item->id,
                'item_name' => $item->name,
                'provider_name' => $item->provider_label,
                'reference_value' => $item->reference_value,
            ]),
        ]);

        return redirect()
            ->route('panel.redemptions.shop')
            ->with('success', 'Resgate solicitado com sucesso. Aguarde a confirmação e o envio do responsável.');
    }
}
