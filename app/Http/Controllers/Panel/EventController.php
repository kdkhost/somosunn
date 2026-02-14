<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $events = Event::where('user_id', $user->id)->latest()->get();
        return view('panel.events.index', compact('events'));
    }

    public function create()
    {
        $event = new Event();
        return view('panel.events.form', compact('event'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'start_at' => 'nullable|date',
            'status' => 'required|in:draft,published,archived,paused',
            'image' => 'nullable|image|max:10240',
        ], [], [
            'title' => 'Título',
            'short_description' => 'Descrição curta',
            'full_description' => 'Descrição completa',
            'start_at' => 'Data',
            'status' => 'Status',
            'image' => 'Imagem',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/event-images'), $fileName);
            $data['image'] = 'uploads/event-images/' . $fileName;
        }

        $data['user_id'] = Auth::id();
        Event::create($data);

        return redirect()->route('panel.events.index')->with('success', 'Evento criado com sucesso!');
    }

    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        return view('panel.events.form', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'full_description' => 'nullable|string',
            'start_at' => 'nullable|date',
            'status' => 'required|in:draft,published,archived,paused',
            'image' => 'nullable|image|max:10240',
        ], [], [
            'title' => 'Título',
            'short_description' => 'Descrição curta',
            'full_description' => 'Descrição completa',
            'start_at' => 'Data',
            'status' => 'Status',
            'image' => 'Imagem',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/event-images'), $fileName);
            $data['image'] = 'uploads/event-images/' . $fileName;
        }

        $event->update($data);
        return redirect()->route('panel.events.index')->with('success', 'Evento atualizado com sucesso!');
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);
        $event->delete();
        return redirect()->route('panel.events.index')->with('success', 'Evento excluído com sucesso!');
    }
}
