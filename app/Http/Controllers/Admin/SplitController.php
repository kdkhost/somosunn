<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderSplit;
use App\Models\Setting;
use App\Services\OrderSplitPayoutService;
use App\Services\Payment\EfiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SplitController extends Controller
{
    protected string $indexView = 'admin.splits.index';

    public function index(Request $request, EfiService $efiService)
    {
        $query = OrderSplit::query()->with(['order', 'receiver', 'payout'])->latest();

        $status = trim((string) $request->query('status', ''));
        if (in_array($status, ['pending', 'processing', 'paid', 'failed'], true)) {
            $query->whereHas('payout', fn ($builder) => $builder->where('status', $status));
        }

        $receiverType = trim((string) $request->query('receiver_type', ''));
        if (in_array($receiverType, ['seller', 'platform', 'traffic', 'superadmin'], true)) {
            $query->where('receiver_type', $receiverType);
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->whereHas('receiver', function ($receiverQuery) use ($search) {
                    $receiverQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });

                if (ctype_digit($search)) {
                    $builder->orWhere('order_id', (int) $search);
                }
            });
        }

        $summary = [
            'total' => (float) (clone $query)->sum('amount'),
            'paid' => (float) (clone $query)->whereHas('payout', fn ($builder) => $builder->where('status', 'paid'))->sum('amount'),
            'pending' => (float) (clone $query)->whereHas('payout', fn ($builder) => $builder->where('status', 'pending'))->sum('amount'),
            'failed' => (float) (clone $query)->whereHas('payout', fn ($builder) => $builder->where('status', 'failed'))->sum('amount'),
            'pending_count' => (int) (clone $query)->whereHas('payout', fn ($builder) => $builder->where('status', 'pending'))->count(),
            'failed_count' => (int) (clone $query)->whereHas('payout', fn ($builder) => $builder->where('status', 'failed'))->count(),
        ];

        $splits = $query->paginate(20)->withQueryString();

        $automation = [
            'enabled' => (string) Setting::get('superadmin_commission_auto_enabled', '0') === '1',
            'mode' => (string) Setting::get('superadmin_commission_payout_mode', 'efi_pix_out'),
            'pix_key' => (string) Setting::get('superadmin_commission_pix_key', auth()->user()?->isSuperAdmin() ? (auth()->user()->pix_key ?? '') : ''),
            'percent' => (float) Setting::get('marketplace_split_superadmin_percent', 10),
            'delay_days' => (int) Setting::get('superadmin_commission_payout_delay_days', 1),
            'min_payout' => (float) Setting::get('superadmin_commission_min_payout', 1.00),
            'efi_enabled' => $efiService->isEnabled(),
            'efi_has_cert' => $efiService->certificatePath() !== '' && is_file($efiService->certificatePath()),
            'is_superadmin' => (bool) auth()->user()?->isSuperAdmin(),
        ];

        return view($this->indexView, compact('splits', 'summary', 'status', 'receiverType', 'search', 'automation'));
    }

    public function saveAutomationSettings(Request $request): JsonResponse
    {
        if (!auth()->user()?->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas o Superadmin pode alterar a automação de comissões.',
            ], 403);
        }

        $validated = $request->validate([
            'auto_enabled' => ['nullable', 'boolean'],
            'payout_mode' => ['required', 'string', 'in:manual,efi_pix_out,mp_routing'],
            'pix_key' => ['nullable', 'string', 'max:255'],
            'percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'delay_days' => ['required', 'integer', 'min:0', 'max:30'],
            'min_payout' => ['required', 'numeric', 'min:0.01', 'max:100000'],
        ]);

        $autoEnabled = (bool) ($validated['auto_enabled'] ?? false);
        $pixKey = trim((string) ($validated['pix_key'] ?? ''));

        if ($autoEnabled && $validated['payout_mode'] === 'efi_pix_out' && $pixKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'Informe a sua chave Pix do Mercado Pago para ativar o envio automático via Efí Pay.',
            ], 422);
        }

        Setting::set('superadmin_commission_auto_enabled', $autoEnabled ? '1' : '0', 'payments');
        Setting::set('superadmin_commission_payout_mode', $validated['payout_mode'], 'payments');
        Setting::set('superadmin_commission_pix_key', $pixKey, 'payments');
        Setting::set('marketplace_split_superadmin_percent', (string) $validated['percent'], 'payments');
        Setting::set('superadmin_commission_payout_delay_days', (string) $validated['delay_days'], 'payments');
        Setting::set('superadmin_commission_min_payout', number_format((float) $validated['min_payout'], 2, '.', ''), 'payments');

        if ($pixKey !== '' && auth()->user()) {
            auth()->user()->forceFill(['pix_key' => $pixKey])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Configurações de automação de comissão salvas com sucesso.',
        ]);
    }

    public function processAuto(OrderSplit $split, OrderSplitPayoutService $payoutService): JsonResponse
    {
        try {
            $payout = $payoutService->processAutomaticPayout($split, true);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        $message = (string) $payout->status === 'paid'
            ? 'Pix automático enviado com sucesso! Comprovante: ' . ($payout->external_id ?: 'registrado')
            : ($payout->notes ?: 'Repasse reagendado automaticamente.');

        return response()->json([
            'success' => true,
            'status' => $payout->status,
            'message' => $message,
        ]);
    }

    public function pay(OrderSplit $split, OrderSplitPayoutService $payoutService): JsonResponse
    {
        try {
            $payoutService->confirmManualPayout($split);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Repasse confirmado e conciliado com sucesso.',
        ]);
    }

    public function fail(Request $request, OrderSplit $split, OrderSplitPayoutService $payoutService): JsonResponse
    {
        try {
            $payoutService->registerFailure($split, (string) $request->input('message', ''));
        } catch (\RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Falha do repasse registrada com sucesso.',
        ]);
    }
}
