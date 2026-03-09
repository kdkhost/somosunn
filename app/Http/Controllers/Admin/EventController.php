<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Admin\Concerns\ManagesContentVisibility;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    use ManagesContentVisibility;

    public function index(Request $request)
    {
        // Todos os membros podem visualizar o calendário
        if ($request->ajax() || $request->wantsJson()) {
            return $this->feed($request);
        }
        $settings = Setting::whereIn('key', ['company_city', 'company_state'])->pluck('value', 'key');
        $companyLocation = ($settings['company_city'] ?? '') . ' ' . ($settings['company_state'] ?? '');
        $calendarSettings = $this->loadCalendarSettings();
        return view('admin.events.calendar', compact('companyLocation', 'calendarSettings'));
    }

    public function show(Event $event)
    {
        return redirect()->route('admin.events.edit', $event);
    }

    public function feed(Request $request)
    {
        $calendarSettings = $this->loadCalendarSettings();
        $textColor = (string) ($calendarSettings['event_text_color'] ?? '#ffffff');

        $startRaw = $request->query('start', $request->input('start'));
        $endRaw = $request->query('end', $request->input('end'));

        try {
            $start = $startRaw ? \Carbon\Carbon::parse($startRaw) : now()->startOfMonth()->subMonth();
            $end = $endRaw ? \Carbon\Carbon::parse($endRaw) : now()->endOfMonth()->addMonth();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Intervalo inválido para o feed de eventos.'], 422);
        }

        $query = Event::where(function ($query) use ($start, $end) {
            $query->where('start_at', '<', $end)
                ->where(function ($q) use ($start) {
                    $q->where(function ($rangeQuery) use ($start) {
                        $rangeQuery->whereNotNull('end_at')
                            ->where('end_at', '>', $start);
                    })->orWhere(function ($singleDayQuery) use ($start) {
                        $singleDayQuery->whereNull('end_at')
                            ->where('start_at', '>=', $start);
                    });
                });
        });

        // Todos os membros podem ver todos os eventos publicados
        // Admins veem todos, membros veem apenas os publicados
        if (!Auth::user()->isAdmin()) {
            $query->where('published', true);
        }

        $events = $query->get();

        $formattedEvents = $events->map(function ($event) use ($textColor) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start, // Uses Model Accessor (ISO8601)
                'end' => $event->end,     // Uses Model Accessor (ISO8601)
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'textColor' => $textColor,
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
                    'is_ticket_enabled' => (bool) $event->is_ticket_enabled,
                ],
            ];
        });

        return response()->json($formattedEvents);
    }

    public function create()
    {
        $this->ensurePermission('events.create');

        return view('admin.events.form', ['event' => new Event]);
    }

    public function edit(Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        return view('admin.events.form', compact('event'));
    }

    public function store(Request $request)
    {
        $this->ensurePermission('events.create');

        foreach (['price', 'flash_sale_price', 'batch_1_price', 'batch_2_price', 'batch_3_price'] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $request->merge([$field => $this->normalizeMoneyInput($request->input($field))]);
        }

        // Sanitize dates (remove T)
        foreach (['start_at', 'end_at', 'flash_sale_ends_at', 'batch_1_deadline', 'batch_2_deadline', 'batch_3_deadline'] as $dateField) {
            if ($request->has($dateField) && $request->$dateField) {
                $request->merge([$dateField => str_replace('T', ' ', $request->$dateField)]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'image' => $this->eventImageRule($request),
            'remove_image' => 'nullable|boolean',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'capacity' => 'nullable|integer|min:0',
            'published' => 'nullable|boolean',
            'all_day' => 'nullable|boolean',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
            'is_certificate_enabled' => 'nullable|boolean',
            'certificate_settings' => 'nullable|json',
            'certificate_bg' => 'nullable|image|max:5120',
            'instructor_signature' => 'nullable|image|max:5120',
            'is_somos_unicas' => 'nullable|boolean',
            'visibility' => 'nullable|string|in:ambos,somos_unn,somos_unicas',
            'event_url' => 'nullable|url|max:255',
            'is_ticket_enabled' => 'nullable|boolean',
        ]);

        $validated['published'] = $request->has('published')
            ? $request->boolean('published')
            : true;

        if (($request->hasFile('image') || $request->boolean('remove_image')) && !Schema::hasColumn('events', 'image')) {
            $message = 'Seu banco de dados está desatualizado: falta a coluna events.image. Atualize o código e rode: php artisan migrate';

            if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
                return back()->with('error', $message);
            }

            return response()->json(['status' => 'error', 'message' => $message], 422);
        }

        if ($request->has('all_day')) {
            $validated['all_day'] = $request->boolean('all_day');
        }

        if (array_key_exists('price', $validated) && $validated['price'] === null) {
            $validated['price'] = 0;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('event-images', 'public');
        }

        // Define o criador do evento
        $data = $validated;
        $data['user_id'] = Auth::id();
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data['is_ticket_enabled'] = $request->boolean('is_ticket_enabled');
        $data = $this->applyVisibilityData($request, $data, null, false, 'events');

        if ($request->hasFile('certificate_bg')) {
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_e_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('instructor_signature')) {
            $file = $request->file('instructor_signature');
            $fileName = 'sig_e_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $fileName);
            $data['instructor_signature'] = 'uploads/signatures/' . $fileName;
        }

        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = is_string($request->certificate_settings) ? json_decode($request->certificate_settings, true) : $request->certificate_settings;
        }

        $event = Event::create($data);

        if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
            return redirect()->route('admin.events.index')->with('success', 'Evento criado com sucesso');
        }

        return response()->json(['status' => 'success', 'message' => 'Evento criado com sucesso', 'event' => $event]);
    }

    public function update(Request $request, Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        foreach (['price', 'flash_sale_price', 'batch_1_price', 'batch_2_price', 'batch_3_price'] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $request->merge([$field => $this->normalizeMoneyInput($request->input($field))]);
        }

        // Sanitize dates (remove T from datetime-local)
        foreach (['start_at', 'end_at', 'flash_sale_ends_at', 'batch_1_deadline', 'batch_2_deadline', 'batch_3_deadline'] as $dateField) {
            if ($request->has($dateField) && $request->$dateField) {
                $request->merge([$dateField => str_replace('T', ' ', $request->$dateField)]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'image' => $this->eventImageRule($request, $event),
            'remove_image' => 'nullable|boolean',
            'location' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'flash_sale_price' => 'nullable|numeric|min:0',
            'flash_sale_ends_at' => 'nullable|date',
            'capacity' => 'nullable|integer|min:0',
            'published' => 'nullable|boolean',
            'all_day' => 'nullable|boolean',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
            'is_certificate_enabled' => 'nullable|boolean',
            'certificate_settings' => 'nullable|json',
            'certificate_bg' => 'nullable|image|max:5120',
            'instructor_signature' => 'nullable|image|max:5120',
            'is_somos_unicas' => 'nullable|boolean',
            'visibility' => 'nullable|string|in:ambos,somos_unn,somos_unicas',
            'event_url' => 'nullable|url|max:255',
            'is_ticket_enabled' => 'nullable|boolean',
        ]);

        if ($request->has('published')) {
            $validated['published'] = $request->boolean('published');
        }

        if (($request->hasFile('image') || $request->boolean('remove_image')) && !Schema::hasColumn('events', 'image')) {
            $message = 'Seu banco de dados está desatualizado: falta a coluna events.image. Atualize o código e rode: php artisan migrate';

            if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
                return back()->with('error', $message);
            }

            return response()->json(['status' => 'error', 'message' => $message], 422);
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

        $data = $validated;
        if ($request->has('is_certificate_enabled')) {
            $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        }
        $data = $this->applyVisibilityData(
            $request,
            $data,
            $event->visibility,
            (bool) $event->is_somos_unicas,
            'events'
        );

        if ($request->hasFile('certificate_bg')) {
            $file = $request->file('certificate_bg');
            $fileName = 'cert_bg_e_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $data['certificate_bg'] = 'uploads/certificates/' . $fileName;
        }

        if ($request->hasFile('instructor_signature')) {
            $file = $request->file('instructor_signature');
            $fileName = 'sig_e_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/signatures'), $fileName);
            $data['instructor_signature'] = 'uploads/signatures/' . $fileName;
        }

        if ($request->has('certificate_settings')) {
            $data['certificate_settings'] = is_string($request->certificate_settings) ? json_decode($request->certificate_settings, true) : $request->certificate_settings;
        }

        $event->update($data);

        if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
            return redirect()->route('admin.events.index')->with('success', 'Evento atualizado');
        }

        return response()->json(['status' => 'success', 'message' => 'Evento atualizado', 'event' => $event]);
    }

    public function destroy(Event $event)
    {
        $this->ensurePermission('events.delete');
        $this->ensureCanManage($event);

        $this->deleteEventImageIfExists($event);
        $event->delete();

        if (!request()->ajax() && !request()->wantsJson() && !request()->expectsJson()) {
            return redirect()->route('admin.events.index')->with('success', 'Evento removido');
        }

        return response()->json(['status' => 'success', 'message' => 'Evento removido']);
    }

    public function updateCalendarSettings(Request $request)
    {
        $payload = $request->validate([
            'initial_view' => 'required|in:dayGridMonth,timeGridWeek,timeGridDay',
            'first_day' => 'required|integer|min:0|max:6',
            'weekends' => 'required|in:0,1',
            'week_numbers' => 'required|in:0,1',
            'button_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'event_text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'recent_limit' => 'required|integer|min:1|max:20',
            'default_remove_after_drop' => 'required|in:0,1',
            'templates' => 'array',
            'templates.*.title' => 'required|string|max:80',
            'templates.*.color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'quick_colors' => 'array',
            'quick_colors.*' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $defaults = $this->defaultCalendarSettings();

        $templates = collect($payload['templates'] ?? [])
            ->map(function ($t) {
                return [
                    'title' => trim((string) ($t['title'] ?? '')),
                    'color' => strtoupper(trim((string) ($t['color'] ?? ''))),
                ];
            })
            ->filter(function ($t) {
                return $t['title'] !== '' && preg_match('/^#[0-9A-F]{6}$/', $t['color']);
            })
            ->values()
            ->all();

        $quickColors = collect($payload['quick_colors'] ?? [])
            ->map(function ($c) {
                return strtoupper(trim((string) $c));
            })
            ->filter(function ($c) {
                return preg_match('/^#[0-9A-F]{6}$/', $c);
            })
            ->unique()
            ->values()
            ->all();

        $settings = [
            'initial_view' => (string) $payload['initial_view'],
            'first_day' => (int) $payload['first_day'],
            'weekends' => (bool) ((int) $payload['weekends']),
            'week_numbers' => (bool) ((int) $payload['week_numbers']),
            'button_color' => strtoupper((string) $payload['button_color']),
            'event_text_color' => strtoupper((string) $payload['event_text_color']),
            'recent_limit' => (int) $payload['recent_limit'],
            'default_remove_after_drop' => (bool) ((int) $payload['default_remove_after_drop']),
            'templates' => $templates ?: ($defaults['templates'] ?? []),
            'quick_colors' => $quickColors ?: ($defaults['quick_colors'] ?? []),
        ];

        Setting::updateOrCreate(
            ['key' => 'calendar_settings_json'],
            ['value' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'group' => 'calendar']
        );

        return response()->json(['status' => 'success', 'message' => 'Configurações do calendário atualizadas.']);
    }

    protected function loadCalendarSettings(): array
    {
        $defaults = $this->defaultCalendarSettings();

        try {
            $raw = (string) (Setting::get('calendar_settings_json', '') ?? '');
            $raw = trim($raw);
            if ($raw === '') {
                return $defaults;
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return $defaults;
            }

            $settings = array_merge($defaults, array_intersect_key($decoded, $defaults));

            $settings['first_day'] = (int) ($settings['first_day'] ?? 0);
            $settings['weekends'] = (bool) ($settings['weekends'] ?? true);
            $settings['week_numbers'] = (bool) ($settings['week_numbers'] ?? false);
            $settings['recent_limit'] = (int) ($settings['recent_limit'] ?? 6);
            $settings['default_remove_after_drop'] = (bool) ($settings['default_remove_after_drop'] ?? false);

            foreach (['button_color', 'event_text_color'] as $key) {
                $value = strtoupper(trim((string) ($settings[$key] ?? '')));
                if (!preg_match('/^#[0-9A-F]{6}$/', $value)) {
                    $settings[$key] = $defaults[$key];
                } else {
                    $settings[$key] = $value;
                }
            }

            $allowedViews = ['dayGridMonth', 'timeGridWeek', 'timeGridDay'];
            if (!in_array((string) ($settings['initial_view'] ?? ''), $allowedViews, true)) {
                $settings['initial_view'] = $defaults['initial_view'];
            }

            $settings['templates'] = is_array($settings['templates'] ?? null) ? $settings['templates'] : $defaults['templates'];
            $settings['quick_colors'] = is_array($settings['quick_colors'] ?? null) ? $settings['quick_colors'] : $defaults['quick_colors'];

            $settings['templates'] = collect($settings['templates'])
                ->map(function ($t) {
                    return [
                        'title' => trim((string) ($t['title'] ?? '')),
                        'color' => strtoupper(trim((string) ($t['color'] ?? ''))),
                    ];
                })
                ->filter(function ($t) {
                    return $t['title'] !== '' && preg_match('/^#[0-9A-F]{6}$/', $t['color']);
                })
                ->values()
                ->all();
            if (empty($settings['templates'])) {
                $settings['templates'] = $defaults['templates'];
            }

            $settings['quick_colors'] = collect($settings['quick_colors'])
                ->map(function ($c) {
                    return strtoupper(trim((string) $c));
                })
                ->filter(function ($c) {
                    return preg_match('/^#[0-9A-F]{6}$/', $c);
                })
                ->unique()
                ->values()
                ->all();
            if (empty($settings['quick_colors'])) {
                $settings['quick_colors'] = $defaults['quick_colors'];
            }

            return $settings;
        } catch (\Throwable $e) {
            return $defaults;
        }
    }

    protected function defaultCalendarSettings(): array
    {
        return [
            'initial_view' => 'dayGridMonth',
            'first_day' => 0,
            'weekends' => true,
            'week_numbers' => false,
            'button_color' => '#1F5EDB',
            'event_text_color' => '#FFFFFF',
            'recent_limit' => 6,
            'default_remove_after_drop' => false,
            'templates' => [
                ['title' => 'Almoço de Negócios', 'color' => '#28A745'],
                ['title' => 'Reunião com Parceiros', 'color' => '#FFC107'],
                ['title' => 'Mentoria VIP', 'color' => '#17A2B8'],
                ['title' => 'Workshop', 'color' => '#007BFF'],
                ['title' => 'Networking', 'color' => '#DC3545'],
            ],
            'quick_colors' => ['#007BFF', '#28A745', '#17A2B8', '#FFC107', '#DC3545', '#6F42C1'],
        ];
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

    protected function eventImageRule(Request $request, ?Event $event = null): string
    {
        $hasCurrentImage = $event && filled($event->image) && !$request->boolean('remove_image');

        return $hasCurrentImage ? 'nullable|image|max:5120' : 'required|image|max:5120';
    }

    /**
     * Verifica se o usuário tem permissão ou é admin.
     */
    protected function ensurePermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->hasPermission($permission))) {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
    }

    /**
     * Verifica se o usuário pode gerenciar o evento (é dono ou admin).
     */
    protected function ensureCanManage(Event $event): void
    {
        if (Auth::user()->isAdmin()) {
            return;
        }

        if (!$event->isOwnedBy(Auth::id())) {
            abort(403, 'Você não tem permissão para gerenciar este evento.');
        }
    }
}
