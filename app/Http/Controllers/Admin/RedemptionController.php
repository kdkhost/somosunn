<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\PointsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedemptionController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $items = RedeemableItem::withCount('redemptions')->latest()->get();
        $pendingRedemptions = Redemption::with(['user', 'item'])->where('status', 'pending')->latest()->get();
        return view('admin.redemptions.index', compact('items', 'pendingRedemptions'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        return view('admin.redemptions.form', ['item' => new RedeemableItem()]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('redemptions', 'public');
        }

        RedeemableItem::create($data);

        return redirect()->route('admin.redemptions.index')->with('success', 'Item de resgate criado com sucesso!');
    }

    public function edit(RedeemableItem $redemption)
    {
        $this->authorizeAdmin();
        // Laravel injected RedeemableItem as $redemption because of the resource name
        return view('admin.redemptions.form', ['item' => $redemption]);
    }

    public function update(Request $request, RedeemableItem $redemption)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('redemptions', 'public');
        }

        $redemption->update($data);

        return redirect()->route('admin.redemptions.index')->with('success', 'Item atualizado com sucesso!');
    }

    public function approve(Redemption $redemption)
    {
        $this->authorizeAdmin();
        $redemption->update(['status' => 'completed']);
        return back()->with('success', 'Resgate concluído!');
    }

    public function cancel(Redemption $redemption)
    {
        $this->authorizeAdmin();

        $user = $redemption->user;
        $user->increment('points', $redemption->points_spent);

        $redemption->update(['status' => 'cancelled']);

        // Log the return of points
        PointsLog::create([
            'user_id' => $user->id,
            'action_key' => 'redemption_cancelled',
            'points' => $redemption->points_spent,
            'meta' => json_encode([
                'redemption_id' => $redemption->id,
                'item_id' => $redemption->redeemable_item_id,
                'item_name' => $redemption->item->name ?? 'Item removido'
            ])
        ]);

        return back()->with('success', 'Resgate cancelado e pontos devolvidos!');
    }

    private function authorizeAdmin()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
