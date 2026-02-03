<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Permission;

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
        return view('admin.plans.form', ['plan'=>new Plan(),'permissions'=>$permissions]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        if($request->hasFile('image')){
            $data['image'] = $request->file('image')->store('plan-images','public');
        }
        Plan::create($data);
        return redirect()->route('admin.plans.index')->with('success','Plano criado');
    }

    public function edit(Plan $plan)
    {
        $permissions = Permission::all();
        return view('admin.plans.form', compact('plan','permissions'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validateData($request, $plan->id);
        if($request->hasFile('image')){
            $data['image'] = $request->file('image')->store('plan-images','public');
        }
        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('success','Plano atualizado');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success','Plano removido');
    }

    protected function validateData(Request $request, $id=null)
    {
        $data = $request->validate([
            'name'=>'required|string|max:120',
            'price'=>'required|numeric|min:0',
            'period'=>'required|string|max:50',
            'highlight'=>'nullable|boolean',
            'coupons_enabled'=>'nullable|boolean',
            'benefits'=>'nullable|array',
            'benefits.*'=>'nullable|string',
            'permissions'=>'nullable|array',
            'permissions.*'=>'integer',
            'is_active'=>'nullable|boolean',
        ]);
        $data['highlight'] = $request->boolean('highlight');
        $data['coupons_enabled'] = $request->boolean('coupons_enabled');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['benefits'] = array_values($request->input('benefits', []));
        $data['permissions'] = $request->input('permissions', []);
        return $data;
    }
}