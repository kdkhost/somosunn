<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mentorship;

class MentorshipController extends Controller
{
    public function index(){ $items = Mentorship::paginate(20); return view('admin.mentorships.index', compact('items')); }
    public function create(){ return view('admin.mentorships.form', ['mentorship' => new Mentorship]); }
    public function store(Request $request){ $data = $request->validate(['title'=>'required','mentor_id'=>'nullable|exists:users,id','price'=>'nullable|numeric','slots'=>'nullable|integer']); Mentorship::create($data); return redirect()->route('admin.mentorships.index')->with('success','Mentoria criada'); }
    public function edit(Mentorship $mentorship){ return view('admin.mentorships.form', compact('mentorship')); }
    public function update(Request $request, Mentorship $mentorship){ $data = $request->validate(['title'=>'required','mentor_id'=>'nullable|exists:users,id','price'=>'nullable|numeric','slots'=>'nullable|integer']); $mentorship->update($data); return redirect()->route('admin.mentorships.index')->with('success','Mentoria atualizada'); }
    public function destroy(Mentorship $mentorship){ $mentorship->delete(); return redirect()->route('admin.mentorships.index')->with('success','Mentoria removida'); }
}