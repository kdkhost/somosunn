<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // FullCalendar event feed
            $events = Event::whereDate('start_at', '>=', $request->start)
                           ->whereDate('end_at', '<=', $request->end)
                           ->whereDate('end_at', '<=', $request->end)
                           ->get(['id', 'title', 'start_at as start', 'end_at as end', 'color', 'all_day', 'address', 'latitude', 'longitude', 'description', 'location', 
                                  'price', 'capacity', 
                                  'batch_1_price', 'batch_1_deadline',
                                  'batch_2_price', 'batch_2_deadline',
                                  'batch_3_price', 'batch_3_deadline']);
            return response()->json($events);
        }
        $settings = \App\Models\Setting::whereIn('key', ['company_city', 'company_state'])->pluck('value', 'key');
        $companyLocation = ($settings['company_city'] ?? '') . ' ' . ($settings['company_state'] ?? '');
        return view('admin.events.calendar', compact('companyLocation'));
    }

    public function create()
    {
        return view('admin.events.form', ['event' => new Event]);
    }

    public function edit(Event $event)
    {
        return view('admin.events.form', compact('event'));
    }

    public function store(Request $request)
    {
        if($request->has('price') && $request->price){
             $request->merge(['price' => str_replace(',', '.', str_replace(['R$ ', '.'], '', $request->price))]);
        }
        // Sanitize batches
        foreach(['batch_1_price', 'batch_2_price', 'batch_3_price'] as $field){
            if($request->has($field) && $request->$field){
                $request->merge([$field => str_replace(',', '.', str_replace(['R$ ', '.'], '', $request->$field))]);
            }
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
        ]);

        $event = Event::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Evento criado com sucesso',
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {
        if($request->has('price') && $request->price){
             $request->merge(['price' => str_replace(',', '.', str_replace(['R$ ', '.'], '', $request->price))]);
        }
        // Sanitize batches
        foreach(['batch_1_price', 'batch_2_price', 'batch_3_price'] as $field){
            if($request->has($field) && $request->$field){
                $request->merge([$field => str_replace(',', '.', str_replace(['R$ ', '.'], '', $request->$field))]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
        ]);

        $event->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Evento atualizado',
            'event' => $event
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(['status' => 'success', 'message' => 'Evento removido']);
    }
}