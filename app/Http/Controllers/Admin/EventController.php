<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\Admin\Concerns\ManagesContentVisibility;
use App\Models\Event;
use App\Models\Setting;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
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
            'start_at' => 'required_if:type,event|nullable|date',
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
            'whatsapp_group_link' => 'nullable|url|max:2048',
            'is_ticket_enabled' => 'nullable|boolean',
            'scanner_restriction_mode' => 'nullable|string|in:disabled,exact,radius',
            'scanner_radius_value' => 'nullable|numeric|min:0.001',
            'scanner_radius_unit' => 'nullable|string|in:m,km',
            'scanner_early_minutes' => 'nullable|integer|min:0|max:1440',
            'scanner_late_minutes' => 'nullable|integer|min:0|max:1440',
            'type' => 'nullable|string|in:event,album',
            'slug' => 'nullable|string|max:255',
        ]);

        $validated['published'] = $request->has('published')
            ? $request->boolean('published')
            : true;

        // Eventos sem imagem ficam automaticamente como rascunho (não publicados).
        // Só são publicados quando a imagem de capa está presente.
        $hasImage = $request->hasFile('image')
            || (!empty($validated['image'] ?? null));
        if (!$hasImage) {
            $validated['published'] = false;
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

        if ($request->hasFile('image')) {
            $validated['image'] = UploadStorage::storeUploadedFile(
                $request->file('image'),
                'event-images',
                null,
                ['prefix' => 'event-cover']
            );
        }

        // Define o criador do evento
        $data = $validated;
        $data['type'] = $request->input('type', 'event');
        if ($data['type'] === 'album' && !isset($data['start_at'])) {
            $data['start_at'] = now();
        }
        $data['slug'] = $request->input('slug')
            ?: Str::slug($data['title']) . '-' . substr(uniqid(), -6);
        $data['user_id'] = Auth::id();
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data['is_ticket_enabled'] = $request->boolean('is_ticket_enabled');
        if (!$this->canManageGroupLink()) {
            unset($data['whatsapp_group_link']);
        }
        $data = $this->applyScannerRestrictionPayload($request, $data);
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

        $isAlbum = ($data['type'] ?? 'event') === 'album';
        $redirectRoute = $isAlbum ? 'admin.events.acervo' : 'admin.events.index';
        $successMsg = $isAlbum ? 'Álbum criado com sucesso' : 'Evento criado com sucesso';

        if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
            return redirect()->route($redirectRoute)->with('success', $successMsg);
        }

        return response()->json(['status' => 'success', 'message' => $successMsg, 'event' => $event]);
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
            'start_at' => 'required_if:type,event|nullable|date',
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
            'whatsapp_group_link' => 'nullable|url|max:2048',
            'is_ticket_enabled' => 'nullable|boolean',
            'scanner_restriction_mode' => 'nullable|string|in:disabled,exact,radius',
            'scanner_radius_value' => 'nullable|numeric|min:0.001',
            'scanner_radius_unit' => 'nullable|string|in:m,km',
            'scanner_early_minutes' => 'nullable|integer|min:0|max:1440',
            'scanner_late_minutes' => 'nullable|integer|min:0|max:1440',
            'type' => 'nullable|string|in:event,album',
            'slug' => 'nullable|string|max:255',
        ]);

        if ($request->has('published')) {
            $validated['published'] = $request->boolean('published');
        } else {
            // Se o campo nao foi enviado (checkbox desmarcada sem hidden input),
            // manter o valor atual do evento para nao forcar despublicacao
            $validated['published'] = $event->published;
        }

        // Eventos sem imagem (nem atual nem nova) ficam como rascunho
        $hasImage = $request->hasFile('image')
            || (filled($event->image) && !$request->boolean('remove_image'));
        if (!$hasImage && ($validated['published'] ?? false)) {
            $validated['published'] = false;
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
            $validated['image'] = UploadStorage::storeUploadedFile(
                $request->file('image'),
                'event-images',
                null,
                ['prefix' => 'event-cover']
            );
        }

        $data = $validated;
        if ($request->has('type')) {
            $data['type'] = $request->input('type');
        }
        if (($data['type'] ?? 'event') === 'album' && (!isset($data['start_at']) || empty($data['start_at']))) {
            $data['start_at'] = now();
        }
        if ($request->has('slug')) {
            $data['slug'] = $request->input('slug')
                ?: ($event->slug ?: Str::slug($data['title']) . '-' . substr(uniqid(), -6));
        }
        if ($request->has('is_certificate_enabled')) {
            $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        }
        $data['is_ticket_enabled'] = $request->boolean('is_ticket_enabled');
        if (!$this->canManageGroupLink()) {
            unset($data['whatsapp_group_link']);
        }
        $data = $this->applyScannerRestrictionPayload($request, $data);
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

        $isAlbum = ($data['type'] ?? $event->type) === 'album';
        $redirectRoute = $isAlbum ? 'admin.events.acervo' : 'admin.events.index';
        $successMsg = $isAlbum ? 'Álbum atualizado com sucesso' : 'Evento atualizado com sucesso';

        if (!$request->ajax() && !$request->wantsJson() && !$request->expectsJson()) {
            return redirect()->route($redirectRoute)->with('success', $successMsg);
        }

        return response()->json(['status' => 'success', 'message' => $successMsg, 'event' => $event]);
    }

    public function destroy(Event $event)
    {
        $this->ensurePermission('events.delete');
        $this->ensureCanManage($event);

        $isAlbum = $event->type === 'album';
        $redirectRoute = $isAlbum ? 'admin.events.acervo' : 'admin.events.index';
        $successMsg = $isAlbum ? 'Álbum apagado com sucesso' : 'Evento removido com sucesso';

        $this->deleteEventImageIfExists($event);
        $event->delete();

        if (!request()->ajax() && !request()->wantsJson() && !request()->expectsJson()) {
            return redirect()->route($redirectRoute)->with('success', $successMsg);
        }

        return response()->json(['status' => 'success', 'message' => $successMsg]);
    }

    /**
     * Alterna o estado de publicacao do evento (publicar/despublicar).
     * Bloqueia publicacao sem imagem de capa.
     */
    public function togglePublished(Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        $newPublished = !$event->published;

        // Bloqueia publicacao sem imagem de capa
        if ($newPublished && empty($event->image)) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Adicione uma imagem de capa antes de publicar este evento.',
                ], 422);
            }
            return back()->with('error', 'Adicione uma imagem de capa antes de publicar este evento.');
        }

        $event->forceFill(['published' => $newPublished])->save();

        $message = $event->published ? 'Evento ativado com sucesso' : 'Evento desativado com sucesso';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'published' => (bool) $event->published,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Move o evento no calendario (drag & drop).
     */
    public function move(Request $request, Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        $request->validate([
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'allDay' => 'nullable|boolean',
        ]);

        $event->update([
            'start_at' => $request->input('start'),
            'end_at' => $request->input('end'),
            'all_day' => $request->boolean('allDay'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Evento movido com sucesso',
        ]);
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
        // Imagem sempre opcional. Eventos sem imagem ficam como rascunho (published=false)
        // e aparecem como "incompletos" até que a imagem seja adicionada.
        return 'nullable|image|max:5120';
    }

    /**
     * Verifica se o usuário tem permissão ou é admin.
     */
    private function applyScannerRestrictionPayload(Request $request, array $data): array
    {
        $mode = (string) ($request->input('scanner_restriction_mode') ?: Event::SCANNER_RESTRICTION_DISABLED);
        if (!in_array($mode, [
            Event::SCANNER_RESTRICTION_DISABLED,
            Event::SCANNER_RESTRICTION_EXACT,
            Event::SCANNER_RESTRICTION_RADIUS,
        ], true)) {
            $mode = Event::SCANNER_RESTRICTION_DISABLED;
        }

        $data['scanner_restriction_mode'] = $mode;
        $data['scanner_radius_meters'] = null;

        if ($mode === Event::SCANNER_RESTRICTION_DISABLED) {
            return $data;
        }

        if (($data['latitude'] ?? null) === null || ($data['longitude'] ?? null) === null) {
            throw ValidationException::withMessages([
                'scanner_restriction_mode' => 'Configure latitude e longitude do evento para ativar a cerca digital do QR Code.',
            ]);
        }

        if ($mode === Event::SCANNER_RESTRICTION_EXACT) {
            return $data;
        }

        $radiusValue = $request->input('scanner_radius_value');
        if (!is_numeric($radiusValue) || (float) $radiusValue <= 0) {
            throw ValidationException::withMessages([
                'scanner_radius_value' => 'Informe a margem de erro da cerca digital em metros ou quilometros.',
            ]);
        }

        $unit = $request->input('scanner_radius_unit') === 'km' ? 'km' : 'm';
        $meters = $unit === 'km'
            ? (int) round(((float) $radiusValue) * 1000)
            : (int) round((float) $radiusValue);

        if ($meters < 1) {
            throw ValidationException::withMessages([
                'scanner_radius_value' => 'A margem da cerca digital precisa ser maior que zero.',
            ]);
        }

        $data['scanner_radius_meters'] = $meters;

        return $data;
    }

    protected function ensurePermission(string $permission): void
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->hasPermission($permission))) {
            abort(403, 'Você não tem permissão para realizar esta ação.');
        }
    }

    /**
     * Verifica se o usuário pode gerenciar todos os eventos.
     */
    protected function canManageGroupLink(): bool
    {
        $user = Auth::user();

        return $user && ($user->isAdmin() || $user->hasPermission('admin.events.group_link.manage'));
    }

    private function canManageAllEvents(): bool
    {
        $user = Auth::user();
        return $user && $user->isAdmin();
    }

    /**
     * Lista os eventos no layout AdminLTE.
     */
    public function list(Request $request)
    {
        $this->ensurePermission('events.view');

        $query = Event::query()->latest();

        // Filtro por tipo (Evento ou Álbum) vindo da rota ou query string
        $type = $request->route('type') ?? $request->query('type');
        if ($type) {
            $query->where('type', $type);
        }

        if (!$this->canManageAllEvents()) {
            $query->where('user_id', Auth::id());
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $events = $query->get();

        return view('admin.events.list', compact('events', 'search', 'type'));
    }

    /**
     * Verifica se o usuário pode gerenciar o evento (é dono ou admin).
     */
    protected function ensureCanManage(Event $event): void
    {
        if ($this->canManageAllEvents()) {
            return;
        }

        if ($event->user_id !== Auth::id()) {
            abort(403, 'Você não tem permissão para gerenciar este evento.');
        }
    }

    /**
     * Alterna o valor de um campo booleano via AJAX.
     */
    public function toggleField(Request $request, Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);
        $field = $request->input('field');
        $allowedFields = ['published', 'show_on_gallery'];
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Campo não permitido para alternância rápida.'], 400);
        }
        $event->$field = !$event->$field;
        $event->save();
        return response()->json([
            'status' => 'success',
            'value' => (bool) $event->$field,
            'message' => 'Alteração salva com sucesso!'
        ]);
    }

    /**
     * Define uma imagem da galeria como capa do evento/álbum.
     */
    public function setCover(Request $request, Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        $request->validate([
            'media_id' => 'required|exists:event_media,id'
        ]);

        $media = \App\Models\EventMedia::where('event_id', $event->id)
            ->where('id', $request->media_id)
            ->where('type', 'image')
            ->firstOrFail();

        $event->update([
            'image' => $media->file_path
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Capa do álbum atualizada com sucesso!'
        ]);
    }
}
