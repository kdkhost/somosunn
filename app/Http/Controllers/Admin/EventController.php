<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $this->feed($request);
        }
        $settings = \App\Models\Setting::whereIn('key', ['company_city', 'company_state'])->pluck('value', 'key');
        $companyLocation = ($settings['company_city'] ?? '') . ' ' . ($settings['company_state'] ?? '');
        return view('admin.events.calendar', compact('companyLocation'));
    }

    public function show(Event $event)
    {
        return redirect()->route('admin.events.edit', $event);
    }

    public function feed(Request $request)
    {
        $startRaw = $request->query('start', $request->input('start'));
        $endRaw = $request->query('end', $request->input('end'));

        try {
            $start = $startRaw ? \Carbon\Carbon::parse($startRaw) : now()->startOfMonth()->subMonth();
            $end = $endRaw ? \Carbon\Carbon::parse($endRaw) : now()->endOfMonth()->addMonth();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Intervalo inválido para o feed de eventos.'], 422);
        }

        $events = Event::where(function ($query) use ($start, $end) {
            $query->where('start_at', '<', $end)
                ->where(function ($q) use ($start) {
                    $q->where('end_at', '>', $start)
                        ->orWhereNull('end_at');
                });
        })->get();

        $formattedEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start, // Uses Model Accessor (ISO8601)
                'end' => $event->end,     // Uses Model Accessor (ISO8601)
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'textColor' => '#ffffff',
                'allDay' => (bool) $event->all_day,
                'extendedProps' => [
                    'description' => $event->description,
                    'image_url' => $event->image ? asset('storage/' . $event->image) : null,
                    'address' => $event->address,
                    'location' => $event->location,
                    'capacity' => $event->capacity,
                    'price' => $event->price,
                    'published' => (bool) $event->published,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                    'batch_1_price' => $event->batch_1_price,
                    'batch_1_deadline' => $event->batch_1_deadline ? \Carbon\Carbon::parse($event->batch_1_deadline)->toIso8601String() : null,
                    'batch_2_price' => $event->batch_2_price,
                    'batch_2_deadline' => $event->batch_2_deadline ? \Carbon\Carbon::parse($event->batch_2_deadline)->toIso8601String() : null,
                    'batch_3_price' => $event->batch_3_price,
                    'batch_3_deadline' => $event->batch_3_deadline ? \Carbon\Carbon::parse($event->batch_3_deadline)->toIso8601String() : null,
                ],
            ];
        });

        return response()->json($formattedEvents);
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
        foreach (['price', 'batch_1_price', 'batch_2_price', 'batch_3_price'] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $request->merge([$field => $this->normalizeMoneyInput($request->input($field))]);
        }
        
        // Sanitize dates (remove T)
        foreach(['start_at', 'end_at', 'batch_1_deadline', 'batch_2_deadline', 'batch_3_deadline'] as $dateField){
            if($request->has($dateField) && $request->$dateField){
                $request->merge([$dateField => str_replace('T', ' ', $request->$dateField)]);
            }
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'published' => 'nullable|boolean',
            'all_day' => 'nullable|boolean',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
        ]);

        $validated['published'] = $request->has('published')
            ? $request->boolean('published')
            : true;

        if ($request->has('all_day')) {
            $validated['all_day'] = $request->boolean('all_day');
        }

        if (array_key_exists('price', $validated) && $validated['price'] === null) {
            $validated['price'] = 0;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('event-images', 'public');
        }

        $event = Event::create($validated);

        if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
            return redirect()->route('admin.events.index')->with('success', 'Evento criado com sucesso');
        }

        return response()->json(['status' => 'success', 'message' => 'Evento criado com sucesso', 'event' => $event]);
    }

    public function update(Request $request, Event $event)
    {
        foreach (['price', 'batch_1_price', 'batch_2_price', 'batch_3_price'] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $request->merge([$field => $this->normalizeMoneyInput($request->input($field))]);
        }
        
        // Sanitize dates (remove T from datetime-local)
        foreach(['start_at', 'end_at', 'batch_1_deadline', 'batch_2_deadline', 'batch_3_deadline'] as $dateField){
            if($request->has($dateField) && $request->$dateField){
                $request->merge([$dateField => str_replace('T', ' ', $request->$dateField)]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'remove_image' => 'nullable|boolean',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'published' => 'nullable|boolean',
            'all_day' => 'nullable|boolean',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
        ]);

        if ($request->has('published')) {
            $validated['published'] = $request->boolean('published');
        }

        if ($request->has('all_day')) {
            $validated['all_day'] = $request->boolean('all_day');
        }

        if (array_key_exists('price', $validated) && $validated['price'] === null) {
            $validated['price'] = 0;
        }

        $removeImage = $request->boolean('remove_image');
        if ($removeImage) {
            $this->deleteEventImageIfExists($event);
            $validated['image'] = null;
        }

        if ($request->hasFile('image')) {
            $this->deleteEventImageIfExists($event);
            $validated['image'] = $request->file('image')->store('event-images', 'public');
        }

        $event->update($validated);

        if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
            return redirect()->route('admin.events.index')->with('success', 'Evento atualizado');
        }

        return response()->json(['status' => 'success', 'message' => 'Evento atualizado', 'event' => $event]);
    }

    public function destroy(Event $event)
    {
        $this->deleteEventImageIfExists($event);
        $event->delete();

        if (!request()->ajax() && !request()->wantsJson() && !request()->expectsJson()) {
            return redirect()->route('admin.events.index')->with('success', 'Evento removido');
        }

        return response()->json(['status' => 'success', 'message' => 'Evento removido']);
    }

    protected function normalizeMoneyInput($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', ' ', "\u{00A0}"], '', $value);

        // Brazilian format: 1.234,56 -> 1234.56
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    protected function deleteEventImageIfExists(Event $event): void
    {
        if (!$event->image) {
            return;
        }

        Storage::disk('public')->delete($event->image);
    }
}
