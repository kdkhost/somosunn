<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\PointsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedemptionItemController extends Controller
{
    public function index()
    {
        $items = RedeemableItem::where('is_active', true)->get();
        return view('panel.redemptions.shop', compact('items'));
    }

    public function history()
    {
        $redemptions = Redemption::with('item')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('panel.redemptions.history', compact('redemptions'));
    }

    public function redeem(Request $request, RedeemableItem $item)
    {
        if (!$item->is_active) {
            return back()->with('error', 'Este item não está mais disponível.');
        }

        if ($item->stock == 0) {
            return back()->with('error', 'Item fora de estoque.');
        }

        $user = Auth::user();

        if ($user->points < $item->points_cost) {
            return back()->with('error', 'Você não tem pontos suficientes.');
        }

        // Deduct points
        $user->decrement('points', $item->points_cost);

        // Update stock if not unlimited
        if ($item->stock > 0) {
            $item->decrement('stock');
        }

        // Create redemption record
        $redemption = Redemption::create([
            'user_id' => $user->id,
            'redeemable_item_id' => $item->id,
            'points_spent' => $item->points_cost,
            'status' => 'pending'
        ]);

        // Log the transaction
        PointsLog::create([
            'user_id' => $user->id,
            'action_key' => 'redemption_requested',
            'points' => -$item->points_cost,
            'meta' => json_encode([
                'redemption_id' => $redemption->id,
                'item_id' => $item->id,
                'item_name' => $item->name
            ])
        ]);

        return redirect()->route('panel.redemptions.shop')->with('success', 'Resgate solicitado com sucesso! Aguarde a aprovação do administrador.');
    }
}
