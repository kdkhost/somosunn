<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderSplit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SplitController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderSplit::query()->with(['order', 'receiver'])->latest();

        $status = trim((string) $request->query('status', ''));
        if (in_array($status, ['pending', 'paid', 'rejected'], true)) {
            $query->where('status', $status);
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
            'paid' => (float) (clone $query)->where('status', 'paid')->sum('amount'),
            'pending' => (float) (clone $query)->where('status', 'pending')->sum('amount'),
            'pending_count' => (int) (clone $query)->where('status', 'pending')->count(),
        ];

        $splits = $query->paginate(20)->withQueryString();

        return view('panel.admin.splits.index', compact('splits', 'summary', 'status', 'receiverType', 'search'));
    }

    public function pay(OrderSplit $split): JsonResponse
    {
        if ($split->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Este rateio ja foi liquidado anteriormente.',
            ], 422);
        }

        if (empty($split->pix_key)) {
            return response()->json([
                'success' => false,
                'message' => 'Cadastre a chave PIX do destinatario antes de liquidar este rateio.',
            ], 422);
        }

        $split->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Rateio liquidado com sucesso.',
        ]);
    }
}
