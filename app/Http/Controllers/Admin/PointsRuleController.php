<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointsRule;

class PointsRuleController extends Controller
{
    public function index()
    {
        $rulesGrouped = PointsRule::grouped();
        $categories = PointsRule::CATEGORIES;
        return view('admin.points.index', compact('rulesGrouped', 'categories'));
    }

    public function create()
    {
        $categories = PointsRule::CATEGORIES;
        return view('admin.points.form', ['rule' => new PointsRule, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|alpha_dash|unique:points_rules,key',
            'label' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'points' => 'required|integer',
            'icon' => 'nullable|string|max:50',
            'repeatable' => 'nullable',
            'max_daily' => 'nullable|integer|min:1',
        ]);

        $data['active'] = true;
        $data['repeatable'] = $request->has('repeatable');
        $data['sort_order'] = PointsRule::max('sort_order') + 1;

        PointsRule::create($data);
        return redirect()->route('admin.points-rules.index')->with('success', 'Regra criada com sucesso!');
    }

    public function edit(PointsRule $points_rule)
    {
        $categories = PointsRule::CATEGORIES;
        return view('admin.points.form', ['rule' => $points_rule, 'categories' => $categories]);
    }

    public function update(Request $request, PointsRule $points_rule)
    {
        $data = $request->validate([
            'label' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:255',
            'points' => 'required|integer',
            'icon' => 'nullable|string|max:50',
            'active' => 'nullable',
            'repeatable' => 'nullable',
            'max_daily' => 'nullable|integer|min:1',
        ]);

        $data['active'] = $request->has('active');
        $data['repeatable'] = $request->has('repeatable');

        $points_rule->update($data);
        return redirect()->route('admin.points-rules.index')->with('success', 'Regra atualizada!');
    }

    public function destroy(PointsRule $points_rule)
    {
        $points_rule->delete();
        return redirect()->route('admin.points-rules.index')->with('success', 'Regra removida!');
    }
}