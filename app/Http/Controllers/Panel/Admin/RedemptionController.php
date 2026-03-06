<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\Setting;
use App\Notifications\RedemptionStatusUpdated;
use App\Services\PointsExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RedemptionController extends Controller
{
    public function __construct(private readonly PointsExchangeService $exchangeService)
    {
    }

    public function index()
    {
        $user = Auth::user();
        $this->ensureManagerAccess();

        $items = $this->managedItemsQuery($user)
            ->withCount('redemptions')
            ->latest()
            ->paginate(12, ['*'], 'items_page')
            ->withQueryString();

        $pendingRedemptions = $this->managedRedemptionsQuery($user)
            ->where('status', 'pending')
            ->latest()
            ->paginate(6, ['*'], 'pending_page')
            ->withQueryString();

        $deliveryRedemptions = $this->managedRedemptionsQuery($user)
            ->whereIn('status', ['processing', 'shipped'])
            ->latest()
            ->paginate(6, ['*'], 'delivery_page')
            ->withQueryString();

        $recentRedemptions = $this->managedRedemptionsQuery($user)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest()
            ->limit(6)
            ->get();

        return view('panel.admin.redemptions.index', [
            'items' => $items,
            'pendingRedemptions' => $pendingRedemptions,
            'deliveryRedemptions' => $deliveryRedemptions,
            'recentRedemptions' => $recentRedemptions,
            'exchangeSettings' => $this->exchangeService->settings(),
            'canManageAllRedemptions' => $user->isAdmin(),
        ]);
    }

    public function create()
    {
        $this->ensureManagerAccess();

        return view('panel.admin.redemptions.form', [
            'item' => new RedeemableItem(),
            'exchangeSettings' => $this->exchangeService->settings(),
            'providerLabel' => $this->providerPayloadForUser(Auth::user())['provider_name'],
            'canManageAllRedemptions' => Auth::user()->isAdmin(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->ensureManagerAccess();

        $data = $this->validatedItemData($request);
        $data = array_merge($data, $this->providerPayloadForUser($user));

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('redemptions', 'public');
        }

        RedeemableItem::create($data);

        return redirect()->route('panel.admin.redemptions.index')->with('success', 'Item de resgate criado com sucesso!');
    }

    public function edit(RedeemableItem $redemption)
    {
        $this->ensureCanManageItem($redemption);

        return view('panel.admin.redemptions.form', [
            'item' => $redemption,
            'exchangeSettings' => $this->exchangeService->settings(),
            'providerLabel' => $redemption->provider_label,
            'canManageAllRedemptions' => Auth::user()->isAdmin(),
        ]);
    }

    public function show(RedeemableItem $redemption)
    {
        $this->ensureCanManageItem($redemption);

        return redirect()->route('panel.admin.redemptions.edit', $redemption);
    }

    public function update(Request $request, RedeemableItem $redemption)
    {
        $this->ensureCanManageItem($redemption);

        $data = $this->validatedItemData($request);
        $data = array_merge($data, $this->providerPayloadForUser(Auth::user(), $redemption));

        if ($request->boolean('remove_image') && $redemption->image) {
            Storage::disk('public')->delete($redemption->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($redemption->image) {
                Storage::disk('public')->delete($redemption->image);
            }

            $data['image'] = $request->file('image')->store('redemptions', 'public');
        }

        $redemption->update($data);

        return redirect()->route('panel.admin.redemptions.index')->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(RedeemableItem $redemption)
    {
        $this->ensureCanManageItem($redemption);

        if ($redemption->image) {
            Storage::disk('public')->delete($redemption->image);
        }

        $redemption->delete();

        return redirect()->route('panel.admin.redemptions.index')->with('success', 'Item removido com sucesso!');
    }

    public function approve(Redemption $redemption)
    {
        $this->ensureCanManageRedemption($redemption);

        if ($redemption->status !== 'pending') {
            return back()->with('warning', 'Esse resgate já foi movimentado.');
        }

        $redemption->update([
            'status' => 'processing',
        ]);

        $redemption->user->notify(new RedemptionStatusUpdated($redemption));

        return back()->with('success', 'Resgate aprovado e liberado para separação/entrega.');
    }

    public function ship(Request $request, Redemption $redemption)
    {
        $this->ensureCanManageRedemption($redemption);

        if (in_array($redemption->status, ['completed', 'cancelled'], true)) {
            return back()->with('warning', 'Esse resgate já foi finalizado.');
        }

        $data = $request->validate([
            'tracking_code' => 'nullable|string|max:120',
            'tracking_url' => 'nullable|url|max:2048',
            'delivery_notes' => 'nullable|string|max:3000',
        ]);

        $redemption->update([
            'status' => 'shipped',
            'tracking_code' => trim((string) ($data['tracking_code'] ?? '')) ?: null,
            'tracking_url' => trim((string) ($data['tracking_url'] ?? '')) ?: null,
            'delivery_notes' => trim((string) ($data['delivery_notes'] ?? '')) ?: $redemption->delivery_notes,
            'shipped_at' => now(),
        ]);

        $redemption->user->notify(new RedemptionStatusUpdated($redemption));

        return back()->with('success', 'Entrega atualizada como enviada.');
    }

    public function complete(Request $request, Redemption $redemption)
    {
        $this->ensureCanManageRedemption($redemption);

        if ($redemption->status === 'cancelled') {
            return back()->with('warning', 'Esse resgate foi cancelado e não pode ser concluído.');
        }

        $data = $request->validate([
            'delivery_notes' => 'nullable|string|max:3000',
        ]);

        $redemption->update([
            'status' => 'completed',
            'delivery_notes' => trim((string) ($data['delivery_notes'] ?? '')) ?: $redemption->delivery_notes,
            'completed_at' => now(),
        ]);

        $redemption->user->notify(new RedemptionStatusUpdated($redemption));

        return back()->with('success', 'Resgate marcado como entregue/concluído.');
    }

    public function cancel(Redemption $redemption)
    {
        $this->ensureCanManageRedemption($redemption);

        if ($redemption->status === 'cancelled') {
            return back()->with('warning', 'Esse resgate já foi cancelado.');
        }

        if ($redemption->status === 'completed') {
            return back()->with('warning', 'Resgates concluídos não podem ser cancelados automaticamente.');
        }

        $user = $redemption->user;
        $user->increment('points', $redemption->points_spent);

        if ($redemption->item && (int) $redemption->item->stock >= 0) {
            $redemption->item->increment('stock');
        }

        $redemption->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $user->notify(new RedemptionStatusUpdated($redemption));

        PointsLog::create([
            'user_id' => $user->id,
            'action_key' => 'redemption_cancelled',
            'points' => $redemption->points_spent,
            'meta' => json_encode([
                'redemption_id' => $redemption->id,
                'item_id' => $redemption->redeemable_item_id,
                'item_name' => $redemption->item->name ?? 'Item removido',
                'provider_name' => $redemption->provider_label,
            ]),
        ]);

        return back()->with('success', 'Resgate cancelado, estoque devolvido e pontos estornados.');
    }

    private function managedItemsQuery($user)
    {
        return RedeemableItem::query()
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('provider_type', 'seller')
                    ->where('provider_user_id', $user->id);
            });
    }

    private function managedRedemptionsQuery($user)
    {
        return Redemption::with(['user', 'item'])
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('provider_type', 'seller')
                    ->where('provider_user_id', $user->id);
            });
    }

    private function validatedItemData(Request $request): array
    {
        $referenceValue = $this->normalizeMoney($request->input('reference_value'));

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reference_value' => 'required',
            'stock' => 'required|integer|min:-1',
            'delivery_lead_days' => 'required|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        $data['reference_value'] = $referenceValue;
        $data['points_cost'] = $this->exchangeService->moneyToPoints($referenceValue);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function normalizeMoney($value): float
    {
        $value = trim((string) $value);
        $value = str_replace(['R$', ' ', "\u{00A0}"], '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return max(0.01, round((float) $value, 2));
    }

    private function providerPayloadForUser($user, ?RedeemableItem $item = null): array
    {
        if ($item && $item->provider_name) {
            return [
                'provider_type' => $item->provider_type,
                'provider_user_id' => $item->provider_user_id,
                'provider_name' => $item->provider_name,
            ];
        }

        if ($user->isAdmin()) {
            return [
                'provider_type' => 'platform',
                'provider_user_id' => null,
                'provider_name' => $this->platformProviderName(),
            ];
        }

        return [
            'provider_type' => 'seller',
            'provider_user_id' => $user->id,
            'provider_name' => trim((string) $user->name),
        ];
    }

    private function platformProviderName(): string
    {
        return (string) (Setting::get('company_name')
            ?: Setting::get('app_name')
            ?: config('app.name', 'SOMOS UNN'));
    }

    private function ensureManagerAccess(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->isAdmin() && !$user->canSellOnMarketplace())) {
            abort(403);
        }
    }

    private function ensureCanManageItem(RedeemableItem $item): void
    {
        $this->ensureManagerAccess();

        if (!$item->canBeManagedBy(Auth::user())) {
            abort(403);
        }
    }

    private function ensureCanManageRedemption(Redemption $redemption): void
    {
        $this->ensureManagerAccess();

        if (!$redemption->canBeManagedBy(Auth::user())) {
            abort(403);
        }
    }
}
