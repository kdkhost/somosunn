<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index()
    {
        $this->ensureAdmin();
        $partners = Partner::withCount(['coupons', 'activeCoupons'])->orderBy('order')->orderBy('name')->get();
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        $this->ensureAdmin();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.partners.form', ['partner' => new Partner, 'users' => $users]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150|unique:partners,slug',
            'website_url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'logo' => 'nullable|image|max:3072',
        ]);

        $data['active'] = $request->boolean('active');
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        if ($request->filled('order')) {
            $data['order'] = (int) $request->order;
        } else {
            // Ordem automática (última posição)
            $data['order'] = (Partner::max('order') ?? 0) + 1;
        }

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partners/logos', 'public');
        }

        Partner::create($data);

        return redirect()->route('admin.partners.index')->with('success', 'Parceiro criado com sucesso!');
    }

    public function edit(Partner $partner)
    {
        $this->ensureAdmin();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);
        $coupons = $partner->coupons()->orderBy('active', 'desc')->orderBy('created_at', 'desc')->get();
        return view('admin.partners.form', compact('partner', 'coupons', 'users'));
    }

    public function update(Request $request, Partner $partner)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150|unique:partners,slug,' . $partner->id,
            'website_url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'logo' => 'nullable|image|max:3072',
            'remove_logo' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        if ($request->filled('order')) {
            $data['order'] = (int) $request->order;
        }

        if ($request->boolean('remove_logo') && $partner->logo) {
            Storage::disk('public')->delete($partner->logo);
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($partner->logo) {
                Storage::disk('public')->delete($partner->logo);
            }
            $data['logo'] = $request->file('logo')->store('partners/logos', 'public');
        }

        unset($data['remove_logo']);
        $partner->update($data);

        return redirect()->route('admin.partners.edit', $partner)->with('success', 'Parceiro atualizado!');
    }

    public function destroy(Partner $partner)
    {
        $this->ensureAdmin();
        if ($partner->logo) {
            Storage::disk('public')->delete($partner->logo);
        }
        $partner->delete();
        return redirect()->route('admin.partners.index')->with('success', 'Parceiro removido.');
    }

    /** Reordenar via AJAX */
    public function updateOrder(Request $request)
    {
        $this->ensureAdmin();
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:partners,id']);
        foreach ($request->order as $position => $id) {
            Partner::where('id', $id)->update(['order' => $position]);
        }
        return response()->json(['status' => 'success', 'message' => 'Ordem de exibição atualizada!']);
    }

    protected function ensureAdmin(): void
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403);
        }
    }
}
