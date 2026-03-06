<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointsRule;
use App\Services\PointsExchangeService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class PointsRuleController extends Controller
{
    public function index()
    {
        $this->ensurePermission('points.view');

        $hasCategory = Schema::hasColumn('points_rules', 'category');
        $query = PointsRule::query();

        if ($hasCategory) {
            $query
                ->orderByRaw("CASE WHEN category IS NULL OR category = '' THEN 1 ELSE 0 END")
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('id');
        } else {
            $query->orderBy('id');
        }

        $rulesPaginator = $query->paginate(20)->withQueryString();
        $rulesCollection = $rulesPaginator->getCollection();
        $rulesGrouped = $hasCategory
            ? $rulesCollection->groupBy(fn (PointsRule $rule) => $rule->category ?: 'outros')
            : collect(['outros' => $rulesCollection]);

        $categories = PointsRule::CATEGORIES;
        $totalRules = $rulesPaginator->total();
        $exchangeSettings = app(PointsExchangeService::class)->settings();
        $canManageExchange = Auth::user()->isAdmin();

        return view('panel.admin.points.index', compact(
            'rulesGrouped',
            'rulesPaginator',
            'categories',
            'hasCategory',
            'totalRules',
            'exchangeSettings',
            'canManageExchange'
        ));
    }

    public function create()
    {
        $this->ensurePermission('points.create');

        $categories = PointsRule::CATEGORIES;
        $hasCategory = Schema::hasColumn('points_rules', 'category');
        return view('panel.admin.points.form', [
            'rule' => new PointsRule,
            'categories' => $categories,
            'hasCategory' => $hasCategory
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('points.create');

        $hasCategory = Schema::hasColumn('points_rules', 'category');

        $rules = [
            'key' => 'required|alpha_dash|unique:points_rules,key',
            'label' => 'required|string|max:100',
            'points' => 'required|integer',
        ];

        if ($hasCategory) {
            $rules['category'] = 'nullable|string|max:50';
            $rules['description'] = 'nullable|string|max:255';
            $rules['icon'] = 'nullable|string|max:50';
            $rules['repeatable'] = 'nullable';
            $rules['max_daily'] = 'nullable|integer|min:1';
        }

        $data = $request->validate($rules);

        $data['active'] = true;
        if ($hasCategory) {
            $data['repeatable'] = $request->has('repeatable');
            $data['sort_order'] = PointsRule::max('sort_order') + 1;
        }

        PointsRule::create($data);
        return redirect()->route('panel.admin.points-rules.index')->with('success', 'Regra criada com sucesso!');
    }

    public function edit(PointsRule $points_rule)
    {
        $this->ensurePermission('points.edit');

        $categories = PointsRule::CATEGORIES;
        $hasCategory = Schema::hasColumn('points_rules', 'category');
        return view('panel.admin.points.form', [
            'rule' => $points_rule,
            'categories' => $categories,
            'hasCategory' => $hasCategory
        ]);
    }

    public function update(Request $request, PointsRule $points_rule)
    {
        $this->ensurePermission('points.edit');

        $hasCategory = Schema::hasColumn('points_rules', 'category');

        $rules = [
            'label' => 'required|string|max:100',
            'points' => 'required|integer',
            'active' => 'nullable',
        ];

        if ($hasCategory) {
            $rules['category'] = 'nullable|string|max:50';
            $rules['description'] = 'nullable|string|max:255';
            $rules['icon'] = 'nullable|string|max:50';
            $rules['repeatable'] = 'nullable';
            $rules['max_daily'] = 'nullable|integer|min:1';
        }

        $data = $request->validate($rules);

        $data['active'] = $request->has('active');
        if ($hasCategory) {
            $data['repeatable'] = $request->has('repeatable');
        }

        $points_rule->update($data);
        return redirect()->route('panel.admin.points-rules.index')->with('success', 'Regra atualizada!');
    }

    public function destroy(PointsRule $points_rule)
    {
        $this->ensurePermission('points.delete');

        $points_rule->delete();
        return redirect()->route('panel.admin.points-rules.index')->with('success', 'Regra removida!');
    }

    public function updateExchangeSettings(Request $request, PointsExchangeService $service)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $baseAmount = trim((string) $request->input('base_amount', '1,00'));
        $baseAmount = str_replace(['R$', ' ', "\u{00A0}"], '', $baseAmount);
        if (str_contains($baseAmount, ',')) {
            $baseAmount = str_replace('.', '', $baseAmount);
            $baseAmount = str_replace(',', '.', $baseAmount);
        }
        $request->merge(['base_amount' => $baseAmount]);

        $data = $request->validate([
            'base_points' => 'required|integer|min:1|max:1000000',
            'base_amount' => 'required|numeric|min:0.01|max:1000000',
        ]);

        $data['base_amount'] = (float) $baseAmount;

        $service->persist($data);

        return redirect()
            ->route('panel.admin.points-rules.index')
            ->with('success', 'Cotação dos pontos atualizada com sucesso.');
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
