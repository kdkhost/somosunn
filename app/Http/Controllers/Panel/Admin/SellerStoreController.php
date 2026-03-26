<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerStore;
use Illuminate\Http\Request;

class SellerStoreController extends Controller
{
    public function index(Request $request)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('panel.admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $search = trim((string) $request->query('q', ''));

        $stores = SellerStore::query()
            ->with('user:id,name,email,plan_id,plan_expires_at')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('brand_name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($users) use ($search) {
                            $users->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('panel.admin.marketplace.stores.index', compact('stores', 'search'));
    }

    public function toggle(Request $request, SellerStore $store)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('panel.admin.dashboard')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $request->validate([
            'is_blocked' => ['required', 'boolean'],
        ]);

        $store->is_blocked = $request->boolean('is_blocked');
        if ($store->is_blocked) {
            $store->is_published = false;
        }
        $store->save();

        return back()->with('success', 'Status da loja atualizado com sucesso.');
    }
}
