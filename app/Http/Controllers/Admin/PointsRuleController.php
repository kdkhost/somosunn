<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointsRule;

class PointsRuleController extends Controller
{
    public function index()
    {
        // Compatibilidade: verifica se a coluna category existe
        $hasCategory = \Schema::hasColumn('points_rules', 'category');
        
        if ($hasCategory) {
            $rulesGrouped = PointsRule::grouped();
        } else {
            // Fallback: agrupa tudo em "outros"
            $rulesGrouped = collect(['outros' => PointsRule::orderBy('id')->get()]);
        }
        
        $categories = PointsRule::CATEGORIES;
        return view('admin.points.index', compact('rulesGrouped', 'categories', 'hasCategory'));
    }

    public function create()
    {
        $categories = PointsRule::CATEGORIES;
        $hasCategory = \Schema::hasColumn('points_rules', 'category');
        return view('admin.points.form', [
            'rule' => new PointsRule,
            'categories' => $categories,
            'hasCategory' => $hasCategory
        ]);
    }

    public function store(Request $request)
    {
        $hasCategory = \Schema::hasColumn('points_rules', 'category');

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
        return redirect()->route('admin.points-rules.index')->with('success', 'Regra criada com sucesso!');
    }

    public function edit(PointsRule $points_rule)
    {
        $categories = PointsRule::CATEGORIES;
        $hasCategory = \Schema::hasColumn('points_rules', 'category');
        return view('admin.points.form', [
            'rule' => $points_rule,
            'categories' => $categories,
            'hasCategory' => $hasCategory
        ]);
    }

    public function update(Request $request, PointsRule $points_rule)
    {
        $hasCategory = \Schema::hasColumn('points_rules', 'category');

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
        return redirect()->route('admin.points-rules.index')->with('success', 'Regra atualizada!');
    }

    public function destroy(PointsRule $points_rule)
    {
        $points_rule->delete();
        return redirect()->route('admin.points-rules.index')->with('success', 'Regra removida!');
    }
}