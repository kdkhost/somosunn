<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PointsRule;

class PointsRuleController extends Controller
{
    public function index()
    {
        $rules = PointsRule::paginate(20);
        return view('admin.points.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.points.form', ['rule' => new PointsRule]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['key'=>'required|alpha_dash|unique:points_rules,key','label'=>'required','points'=>'required|integer']);
        PointsRule::create($data);
        return redirect()->route('admin.points-rules.index')->with('success','Regra salva');
    }

    public function edit(PointsRule $points_rule)
    {
        return view('admin.points.form', ['rule' => $points_rule]);
    }

    public function update(Request $request, PointsRule $points_rule)
    {
        $data = $request->validate(['label'=>'required','points'=>'required|integer','active'=>'nullable']);
        $points_rule->update(array_merge($data, ['active' => $request->has('active')]));
        return redirect()->route('admin.points-rules.index')->with('success','Regra atualizada');
    }

    public function destroy(PointsRule $points_rule)
    {
        $points_rule->delete();
        return redirect()->route('admin.points-rules.index')->with('success','Regra removida');
    }
}