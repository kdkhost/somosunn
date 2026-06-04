<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderSplit;
use Illuminate\Http\Request;

class SplitController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderSplit::with(['order', 'receiver'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('receiver', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('order', function ($q) use ($search) {
                $q->where('id', $search);
            });
        }

        $splits = $query->paginate(20);

        return view('admin.splits.index', compact('splits'));
    }

    public function pay(OrderSplit $split)
    {
        if ($split->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Este rateio já foi liquidado anteriormente.'
            ], 422);
        }

        if (empty($split->pix_key)) {
            return response()->json([
                'success' => false,
                'message' => 'Cadastre a chave PIX do destinatario antes de liquidar este rateio.'
            ], 422);
        }

        $split->update([
            'status' => 'paid'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rateio liquidado com sucesso!'
        ]);
    }
}
