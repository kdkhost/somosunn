<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index(){ $events = Event::paginate(20); return view('admin.events.index', compact('events')); }
    public function create(){ return view('admin.events.form', ['event' => new Event]); }
    public function store(Request $request){ $data = $request->validate(['title'=>'required','start_at'=>'nullable','end_at'=>'nullable','price'=>'nullable|numeric','published'=>'nullable|boolean']); Event::create($data); return redirect()->route('admin.events.index')->with('success','Evento criado'); }
    public function edit(Event $event){ return view('admin.events.form', compact('event')); }
    public function update(Request $request, Event $event){ $data = $request->validate(['title'=>'required','start_at'=>'nullable','end_at'=>'nullable','price'=>'nullable|numeric','published'=>'nullable|boolean']); $event->update($data); return redirect()->route('admin.events.index')->with('success','Evento atualizado'); }
    public function destroy(Event $event){ $event->delete(); return redirect()->route('admin.events.index')->with('success','Evento removido'); }
}