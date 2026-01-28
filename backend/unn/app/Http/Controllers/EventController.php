<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller {
    public function index() {
        $events = Event::orderBy('date', 'desc')->get();
        return Inertia::render('Events/Index', compact('events'));
    }

    public function create() {
        return Inertia::render('Events/Create');
    }

    public function store(Request $request) {
        $data = $request->validate([ 'title'=>'required','date'=>'required' ]);
        Event::create($data);
        return redirect()->route('events.index');
    }

    public function edit(Event $event) {
        return Inertia::render('Events/Edit', compact('event'));
    }

    public function update(Request $request, Event $event) {
        $event->update($request->all());
        return redirect()->route('events.index');
    }

    public function destroy(Event $event) {
        $event->delete();
        return back();
    }
}
