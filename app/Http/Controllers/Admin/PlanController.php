<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderByDesc('highlight')->orderBy('price')->paginate(12);
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.plans.form', ['plan' => new Plan(), 'permissions' => $permissions]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('plan-images', 'public');
        }
        DB::transaction(function () use ($data) {
            $plan = Plan::create($data);
            $this->enforceSingleHighlight($plan);
        });
        return redirect()->route('admin.plans.index')->with('success', 'Plano criado');
    }

    public function edit(Plan $plan)
    {
        $permissions = Permission::all();
        return view('admin.plans.form', compact('plan', 'permissions'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validateData($request, $plan->id);
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('plan-images', 'public');
        }
        DB::transaction(function () use ($plan, $data) {
            $plan->update($data);
            $this->enforceSingleHighlight($plan);
        });
        return redirect()->route('admin.plans.index')->with('success', 'Plano atualizado');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plano removido');
    }

    protected function validateData(Request $request, $id = null)
    {
        if ($request->has('price')) {
            $price = trim((string) $request->input('price'));
            $price = str_replace(['R$', ' ', "\u{00A0}"], '', $price);

            // If brazilian format, normalize: 1.234,56 -> 1234.56
            if (str_contains($price, ',')) {
                $price = str_replace('.', '', $price);
                $price = str_replace(',', '.', $price);
            }

            $request->merge(['price' => $price]);
        }

        if ($request->has('slug')) {
            $request->merge(['slug' => trim((string) $request->input('slug'))]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => [
                'nullable',
                'string',
                'max:160',
                Rule::unique('plans', 'slug')->ignore($id),
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'period' => 'required|string|max:50',
            'highlight' => 'nullable|boolean',
            'coupons_enabled' => 'nullable|boolean',
            'benefits' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = $this->generateUniqueSlug($data['slug'] ?: $data['name'], $id);
        $data['highlight'] = $request->boolean('highlight');
        $data['is_featured'] = $data['highlight'];
        $data['coupons_enabled'] = $request->boolean('coupons_enabled');
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle benefits from textarea
        $benefitsRaw = $request->input('benefits', '');
        $data['benefits'] = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $benefitsRaw))));

        $data['permissions'] = $request->input('permissions', []);

        return $data;
    }

    private function enforceSingleHighlight(Plan $plan): void
    {
        if (!$plan->highlight) {
            return;
        }

        Plan::query()
            ->where('id', '!=', $plan->id)
            ->where(function ($q) {
                $q->where('highlight', true)->orWhere('is_featured', true);
            })
            ->update([
                'highlight' => false,
                'is_featured' => false,
            ]);
    }

    private function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = 'plano';
        }

        $slug = $base;
        $suffix = 2;

        while (
            Plan::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }
}
