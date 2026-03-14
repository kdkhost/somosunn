<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointsRule;
use App\Services\PointsExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PointsRuleController extends Controller
{
    public function index()
    {
        $hasCategory = Schema::hasColumn('points_rules', 'category');
        $rulesGrouped = $hasCategory
            ? PointsRule::grouped()
            : collect(['outros' => PointsRule::orderBy('id')->get()]);

        $categories = PointsRule::CATEGORIES;
        $exchangeSettings = app(PointsExchangeService::class)->settings();

        return view('admin.points.index', compact('rulesGrouped', 'categories', 'hasCategory', 'exchangeSettings'));
    }

    public function create()
    {
        $categories = PointsRule::CATEGORIES;
        $hasCategory = Schema::hasColumn('points_rules', 'category');

        return view('admin.points.form', [
            'rule' => new PointsRule(),
            'categories' => $categories,
            'hasCategory' => $hasCategory,
        ]);
    }

    public function store(Request $request)
    {
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

        return response()->json(['redirect' => route('admin.points-rules.index'), 'message' => 'Regra criada com sucesso!']);
    }

    public function edit(PointsRule $points_rule)
    {
        $categories = PointsRule::CATEGORIES;
        $hasCategory = Schema::hasColumn('points_rules', 'category');

        return view('admin.points.form', [
            'rule' => $points_rule,
            'categories' => $categories,
            'hasCategory' => $hasCategory,
        ]);
    }

    public function update(Request $request, PointsRule $points_rule)
    {
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

        return response()->json(['redirect' => route('admin.points-rules.index'), 'message' => 'Regra atualizada!']);
    }

    public function destroy(PointsRule $points_rule)
    {
        $points_rule->delete();

        return response()->json(['ok' => true, 'message' => 'Regra removida!']);
    }

    public function updateExchangeSettings(Request $request, PointsExchangeService $service)
    {
        $basePoints = max(1, (int) $request->input('base_points', $service->getBasePoints()));
        $unitValue = $this->normalizeDecimalInput($request->input('unit_value', ''));
        $baseAmount = $this->normalizeDecimalInput($request->input('base_amount', ''));
        $usdReferenceRate = $this->normalizeDecimalInput($request->input('usd_reference_rate', '1'));

        if ($unitValue <= 0 && $baseAmount > 0) {
            $unitValue = round($baseAmount / $basePoints, 4);
        }

        $request->merge([
            'base_points' => $basePoints,
            'unit_value' => $unitValue,
            'base_amount' => $baseAmount,
            'usd_reference_rate' => $usdReferenceRate,
        ]);

        $data = $request->validate([
            'base_points' => 'required|integer|min:1|max:1000000',
            'unit_value' => 'required|numeric|min:0.0001|max:1000000',
            'base_amount' => 'nullable|numeric|min:0.01|max:1000000',
            'usd_reference_rate' => 'nullable|numeric|min:0.0001|max:1000000',
            'market_note' => 'nullable|string|max:500',
        ]);

        $service->persist($data);

        return response()->json(['reload' => true, 'message' => 'Cotacao do UNNBIT atualizada com sucesso.']);
    }

    private function normalizeDecimalInput(mixed $value): float
    {
        $value = trim((string) $value);
        $value = str_replace(['R$', ' ', "\u{00A0}"], '', $value);

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return round((float) $value, 4);
    }
}
