<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointsLog;
use App\Models\RedeemableItem;
use App\Models\Redemption;
use App\Models\Setting;
use App\Notifications\RedemptionStatusUpdated;
use App\Services\PointsExchangeService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RedemptionController extends Controller
{
    public function __construct(private readonly PointsExchangeService $exchangeService)
    {
    }

    public function index()
    {
        $this->authorizeAdmin();

        $items = RedeemableItem::withCount('redemptions')->latest()->get();
        $pendingRedemptions = Redemption::with(['user', 'item'])->where('status', 'pending')->latest()->get();

        return view('admin.redemptions.index', [
            'items' => $items,
            'pendingRedemptions' => $pendingRedemptions,
            'exchangeSettings' => $this->exchangeService->settings(),
        ]);
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('admin.redemptions.form', [
            'item' => new RedeemableItem(),
            'exchangeSettings' => $this->exchangeService->settings(),
            'providerLabel' => $this->platformProviderName(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $data = $this->validatedItemData($request);
        $data['provider_type'] = 'platform';
        $data['provider_user_id'] = null;
        $data['provider_name'] = $this->platformProviderName();

        if ($request->hasFile('image')) {
            $data['image'] = UploadStorage::storeUploadedFile($request->file('image'), 'redemptions');
        }

        RedeemableItem::create($data);

        return response()->json(['redirect' => route('admin.redemptions.index'), 'message' => 'Item de resgate criado com sucesso!']);
    }

    public function edit(RedeemableItem $redemption)
    {
        $this->authorizeAdmin();

        return view('admin.redemptions.form', [
            'item' => $redemption,
            'exchangeSettings' => $this->exchangeService->settings(),
            'providerLabel' => $redemption->provider_label,
        ]);
    }

    public function update(Request $request, RedeemableItem $redemption)
    {
        $this->authorizeAdmin();

        $data = $this->validatedItemData($request);
        $data['provider_type'] = $redemption->provider_type ?: 'platform';
        $data['provider_user_id'] = $redemption->provider_user_id;
        $data['provider_name'] = $redemption->provider_name ?: $this->platformProviderName();

        if ($request->boolean('remove_image') && $redemption->image) {
            UploadStorage::delete($redemption->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($redemption->image) {
                UploadStorage::delete($redemption->image);
            }

            $data['image'] = UploadStorage::storeUploadedFile($request->file('image'), 'redemptions');
        }

        $redemption->update($data);

        return response()->json(['redirect' => route('admin.redemptions.index'), 'message' => 'Item atualizado com sucesso!']);
    }

    public function approve(Redemption $redemption)
    {
        $this->authorizeAdmin();

        $redemption->update(['status' => 'completed']);
        $redemption->user?->notify(new RedemptionStatusUpdated($redemption));

        return response()->json(['ok' => true, 'message' => 'Resgate concluido!']);
    }

    public function cancel(Redemption $redemption)
    {
        $this->authorizeAdmin();

        $user = $redemption->user;
        $user->increment('points', $redemption->points_spent);

        if ($redemption->item && (int) $redemption->item->stock >= 0) {
            $redemption->item->increment('stock');
        }

        $redemption->update(['status' => 'cancelled']);
        $user->notify(new RedemptionStatusUpdated($redemption));

        PointsLog::create([
            'user_id' => $user->id,
            'action_key' => 'redemption_cancelled',
            'points' => $redemption->points_spent,
            'meta' => json_encode([
                'redemption_id' => $redemption->id,
                'item_id' => $redemption->redeemable_item_id,
                'item_name' => $redemption->item->name ?? 'Item removido',
                'coin_name' => $this->exchangeService->settings()['coin_name'],
                'provider_name' => $redemption->provider_label,
            ]),
        ]);

        return response()->json(['ok' => true, 'message' => 'Resgate cancelado, estoque devolvido e saldo estornado!']);
    }

    private function validatedItemData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reference_value' => 'nullable',
            'points_cost' => 'nullable|integer|min:1',
            'stock' => 'required|integer|min:-1',
            'delivery_lead_days' => 'required|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'item_type' => 'nullable|in:physical,digital,service',
            'fulfillment_instructions' => 'nullable|string|max:5000',
        ]);

        $manualPoints = max(0, (int) ($data['points_cost'] ?? 0));
        $referenceValue = $request->filled('reference_value')
            ? $this->normalizeMoney($request->input('reference_value'))
            : $this->exchangeService->pointsToMoney($manualPoints);

        $data['reference_value'] = $referenceValue;
        $data['points_cost'] = $manualPoints > 0
            ? $manualPoints
            : $this->exchangeService->moneyToPoints($referenceValue);
        $data['is_active'] = $request->boolean('is_active', true);

        if (!Schema::hasColumn('redeemable_items', 'item_type')) {
            unset($data['item_type']);
        } else {
            $data['item_type'] = $data['item_type'] ?? ($request->input('item_type') ?: 'service');
        }

        if (!Schema::hasColumn('redeemable_items', 'fulfillment_instructions')) {
            unset($data['fulfillment_instructions']);
        }

        return $data;
    }

    private function normalizeMoney(mixed $value): float
    {
        $value = trim((string) $value);
        $value = str_replace(['R$', ' ', "\u{00A0}"], '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return max(0.01, round((float) $value, 2));
    }

    private function platformProviderName(): string
    {
        return (string) (Setting::get('company_name')
            ?: Setting::get('app_name')
            ?: config('app.name', 'SOMOS UNN'));
    }

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
