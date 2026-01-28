<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChildController extends Controller {
    public function index() {
        $children = Child::with('event','responsible')->get();
        return Inertia::render('Children/Index', compact('children'));
    }

    public function create() {
        return Inertia::render('Children/Create');
    }

    public function store(Request $request) {
        $data = $request->validate(['name'=>'required']);
        Child::create($data);
        return redirect()->route('children.index');
    }

    public function edit(Child $child) {
        return Inertia::render('Children/Edit', compact('child'));
    }

    public function update(Request $request, Child $child) {
        $child->update($request->all());
        return redirect()->route('children.index');
    }

    public function destroy(Child $child) {
        $child->delete();
        return back();
    }
}
