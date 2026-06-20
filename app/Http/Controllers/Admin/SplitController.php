<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderSplit;
use App\Services\OrderSplitPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SplitController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderSplit::query()->with(['order', 'receiver', 'payout'])->latest();

        $status = trim((string) $request->query('status', ''));
        if (in_array($status, ['pending', 'paid', 'failed'], true)) {
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

        return view('admin.splits.index', compact('splits', 'summary', 'status', 'receiverType', 'search'));
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
