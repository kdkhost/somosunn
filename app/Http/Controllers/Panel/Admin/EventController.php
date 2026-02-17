<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('events.view');

        if ($request->ajax() || $request->wantsJson()) {
            return $this->feed($request);
        }

        $calendarSettings = $this->loadCalendarSettings();
        return view('panel.admin.events.index', compact('calendarSettings'));
    }

    public function feed(Request $request)
    {
        $calendarSettings = $this->loadCalendarSettings();
        $textColor = (string) ($calendarSettings['event_text_color'] ?? '#ffffff');

        try {
            $start = $request->query('start') ? \Carbon\Carbon::parse($request->query('start')) : now()->startOfMonth();
            $end = $request->query('end') ? \Carbon\Carbon::parse($request->query('end')) : now()->endOfMonth();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Intervalo inválido.'], 422);
        }

        $query = Event::where(function ($query) use ($start, $end) {
            $query->where('start_at', '<', $end)
                ->where(function ($q) use ($start) {
                    $q->where('end_at', '>', $start)->orWhereNull('end_at');
                });
        });

        if (!Auth::user()->isAdmin()) {
            $query->where('published', true);
        }

        $events = $query->get()->map(function ($event) use ($textColor) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'textColor' => $textColor,
                'allDay' => (bool) $event->all_day,
                'extendedProps' => [
                    'description' => $event->description,
                    'published' => (bool) $event->published,
                ],
            ];
        });

        return response()->json($events);
    }

    public function create()
    {
        $this->ensurePermission('events.create');
        return view('panel.admin.events.form', ['event' => new Event]);
    }

    public function edit(Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);
        return view('panel.admin.events.form', compact('event'));
    }

    public function store(Request $request)
    {
        $this->ensurePermission('events.create');
        $data = $this->serializeRequest($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('event-images', 'public');
        }

        $data['user_id'] = Auth::id();
        $event = Event::create($data);

        return redirect()->route('panel.admin.events.index')->with('success', 'Evento criado com sucesso');
    }

    public function update(Request $request, Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);
        $data = $this->serializeRequest($request);

        if ($request->boolean('remove_image')) {
            if ($event->image)
                Storage::disk('public')->delete($event->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($event->image)
                Storage::disk('public')->delete($event->image);
            $data['image'] = $request->file('image')->store('event-images', 'public');
        }

        $event->update($data);
        return redirect()->route('panel.admin.events.index')->with('success', 'Evento atualizado');
    }

    public function destroy(Event $event)
    {
        $this->ensurePermission('events.delete');
        $this->ensureCanManage($event);
        if ($event->image)
            Storage::disk('public')->delete($event->image);
        $event->delete();

        return response()->json(['status' => 'success']);
    }

    private function serializeRequest(Request $request)
    {
        // Normalize money and dates
        $moneyFields = ['price', 'flash_sale_price', 'batch_1_price', 'batch_2_price', 'batch_3_price'];
        foreach ($moneyFields as $f) {
            if ($request->has($f))
                $request->merge([$f => $this->normalizeMoney($request->$f)]);
        }

        $dateFields = ['start_at', 'end_at', 'flash_sale_ends_at', 'batch_1_deadline', 'batch_2_deadline', 'batch_3_deadline'];
        foreach ($dateFields as $f) {
            if ($request->has($f) && $request->$f)
                $request->merge([$f => str_replace('T', ' ', $request->$f)]);
        }

        return $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|integer|min:0',
            'published' => 'nullable|boolean',
            'all_day' => 'nullable|boolean',
            'is_certificate_enabled' => 'nullable|boolean',
            'certificate_settings' => 'nullable|json',
        ]);
    }

    private function normalizeMoney($val)
    {
        if (!$val)
            return 0;
        $val = str_replace(['R$', ' ', '.'], '', $val);
        $val = str_replace(',', '.', $val);
        return (float) $val;
    }

    private function loadCalendarSettings()
    {
        return [
            'initial_view' => 'dayGridMonth',
            'event_text_color' => '#FFFFFF',
            'button_color' => '#1F5EDB',
        ];
    }

    protected function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm))
            abort(403);
    }

    protected function ensureCanManage(Event $event)
    {
        if (Auth::user()->isAdmin())
            return;
        if ($event->user_id !== Auth::id())
            abort(403);
    }
}
