<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Panel\Admin\Concerns\ManagesContentVisibility;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    use ManagesContentVisibility;

    public function index(Request $request)
    {
        $this->ensurePermission('events.view');

        if ($request->ajax() || $request->wantsJson() || $request->boolean('feed')) {
            return $this->feed($request);
        }

        $calendarSettings = $this->loadCalendarSettings();

        return view('panel.admin.events.index', compact('calendarSettings'));
    }

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

        return view('panel.admin.events.list', compact('events', 'search', 'type'));
    }

    public function feed(Request $request)
    {
        $startRaw = $request->query('start', $request->input('start'));
        $endRaw = $request->query('end', $request->input('end'));

        try {
            $start = $startRaw ? \Carbon\Carbon::parse($startRaw) : now()->startOfMonth()->subMonth();
            $end = $endRaw ? \Carbon\Carbon::parse($endRaw) : now()->endOfMonth()->addMonth();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Intervalo inválido.'], 422);
        }

        $query = Event::where('type', 'event')->where(function ($query) use ($start, $end) {
            $query->where('start_at', '<', $end)
                ->where(function ($subQuery) use ($start) {
                    $subQuery->where(function ($rangeQuery) use ($start) {
                        $rangeQuery->whereNotNull('end_at')
                            ->where('end_at', '>', $start);
                    })->orWhere(function ($singleDayQuery) use ($start) {
                        $singleDayQuery->whereNull('end_at')
                            ->where('start_at', '>=', $start);
                    });
                });
        });

        if (!$this->canManageAllEvents()) {
            $query->where('user_id', Auth::id());
        }

        $events = $query->get()->map(function (Event $event) {
            $backgroundColor = $this->normalizeHexColor((string) ($event->color ?: '#3b82f6'));
            $textColor = $this->resolveContrastColor($backgroundColor);

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'textColor' => $textColor,
                'allDay' => (bool) $event->all_day,
                'extendedProps' => [
                    'description' => $event->description,
                    'published' => (bool) $event->published,
                    'editUrl' => route('panel.admin.events.edit', $event),
                ],
            ];
        });

        return response()->json($events);
    }

    public function show(Event $event)
    {
        $this->ensurePermission('events.view');
        $this->ensureCanManage($event);

        return redirect()->route('panel.admin.events.edit', $event);
    }

    public function create()
    {
        $this->ensurePermission('events.create');

        return view('panel.admin.events.form', ['event' => new Event()]);
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
        if (($data['type'] ?? 'event') === 'album' && (!isset($data['start_at']) || empty($data['start_at']))) {
            $data['start_at'] = now();
        }

        if ($request->hasFile('image')) {
            $data['image'] = UploadStorage::storeUploadedFile(
                $request->file('image'),
                'event-images',
                null,
                ['prefix' => 'event-cover']
            );
        }

        if ($request->hasFile('certificate_bg')) {
            $data['certificate_bg'] = UploadStorage::storeUploadedFile(
                $request->file('certificate_bg'),
                'uploads/certificates',
                'cert_bg_' . time() . '_' . uniqid() . '.' . $request->file('certificate_bg')->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('instructor_signature')) {
            $data['instructor_signature'] = UploadStorage::storeUploadedFile(
                $request->file('instructor_signature'),
                'uploads/signatures',
                'sig_' . time() . '_' . uniqid() . '.' . $request->file('instructor_signature')->getClientOriginalExtension()
            );
        }

        $data['published'] = $request->boolean('published');
        $data['all_day'] = $request->boolean('all_day');
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data['is_ticket_enabled'] = $request->boolean('is_ticket_enabled');
        $data['user_id'] = Auth::id();

        // Eventos sem imagem ficam como rascunho (não publicados)
        if (empty($data['image'] ?? null) && !$request->hasFile('image')) {
            $data['published'] = false;
        }

        $data = $this->applyVisibilityData($request, $data, null, false, 'events');

        Event::create($data);

        $isAlbum = ($data['type'] ?? 'event') === 'album';
        $redirectRoute = $isAlbum ? 'panel.admin.events.acervo' : 'panel.admin.events.index';
        $successMsg = $isAlbum ? 'Álbum criado com sucesso' : 'Evento criado com sucesso';

        return redirect()->route($redirectRoute)->with('success', $successMsg);
    }

    public function update(Request $request, Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        $data = $this->serializeRequest($request, $event);
        if (($data['type'] ?? 'event') === 'album' && (!isset($data['start_at']) || empty($data['start_at']))) {
            $data['start_at'] = now();
        }

        if ($request->boolean('remove_image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }

            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }

            $data['image'] = UploadStorage::storeUploadedFile(
                $request->file('image'),
                'event-images',
                null,
                ['prefix' => 'event-cover']
            );
        }

        if ($request->hasFile('certificate_bg')) {
            UploadStorage::delete($event->certificate_bg);
            $data['certificate_bg'] = UploadStorage::storeUploadedFile(
                $request->file('certificate_bg'),
                'uploads/certificates',
                'cert_bg_' . time() . '_' . uniqid() . '.' . $request->file('certificate_bg')->getClientOriginalExtension()
            );
        }

        if ($request->hasFile('instructor_signature')) {
            UploadStorage::delete($event->instructor_signature);
            $data['instructor_signature'] = UploadStorage::storeUploadedFile(
                $request->file('instructor_signature'),
                'uploads/signatures',
                'sig_' . time() . '_' . uniqid() . '.' . $request->file('instructor_signature')->getClientOriginalExtension()
            );
        }

        $data['published'] = $request->boolean('published');
        $data['all_day'] = $request->boolean('all_day');
        $data['is_certificate_enabled'] = $request->boolean('is_certificate_enabled');
        $data['is_ticket_enabled'] = $request->boolean('is_ticket_enabled');

        // Eventos sem imagem (nem atual nem nova) ficam como rascunho
        $hasImage = $request->hasFile('image')
            || (filled($event->image) && !$request->boolean('remove_image'));
        if (!$hasImage && ($data['published'] ?? false)) {
            $data['published'] = false;
        }

        $data = $this->applyVisibilityData(
            $request,
            $data,
            $event->visibility,
            (bool) $event->is_somos_unicas,
            'events'
        );

        $event->update($data);

        $isAlbum = ($data['type'] ?? $event->type) === 'album';
        $redirectRoute = $isAlbum ? 'panel.admin.events.acervo' : 'panel.admin.events.index';
        $successMsg = $isAlbum ? 'Álbum atualizado com sucesso' : 'Evento atualizado com sucesso';

        return redirect()->route($redirectRoute)->with('success', $successMsg);
    }

    public function togglePublished(Event $event)
    {
        $this->ensurePermission('events.edit');
        $this->ensureCanManage($event);

        $newPublished = !$event->published;

        // Bloqueia publicação sem imagem
        if ($newPublished && empty($event->image)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Adicione uma imagem de capa antes de publicar este evento.',
                ], 422);
            }
            return back()->with('error', 'Adicione uma imagem de capa antes de publicar este evento.');
        }

        $event->forceFill([
            'published' => $newPublished,
        ])->save();

        $message = $event->published ? 'Evento ativado com sucesso' : 'Evento desativado com sucesso';

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'published' => (bool) $event->published,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

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

    public function destroy(Event $event)
    {
        $this->ensurePermission('events.delete');
        $this->ensureCanManage($event);

        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        UploadStorage::delete($event->certificate_bg);
        UploadStorage::delete($event->instructor_signature);

        $event->delete();

        if (request()->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('panel.admin.events.index')->with('success', 'Evento removido com sucesso');
    }

    private function serializeRequest(Request $request, ?Event $event = null): array
    {
        $moneyFields = ['price', 'flash_sale_price', 'batch_1_price', 'batch_2_price', 'batch_3_price'];
        foreach ($moneyFields as $field) {
            if ($request->has($field)) {
                $request->merge([$field => $this->normalizeMoney($request->$field)]);
            }
        }

        $dateFields = ['start_at', 'end_at', 'flash_sale_ends_at', 'batch_1_deadline', 'batch_2_deadline', 'batch_3_deadline'];
        foreach ($dateFields as $field) {
            if ($request->has($field) && $request->$field) {
                $request->merge([$field => str_replace('T', ' ', $request->$field)]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required_if:type,event|nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'image' => $this->resolveImageRule($request, $event),
            'certificate_bg' => 'nullable|image|max:5120',
            'instructor_signature' => 'nullable|image|max:2048|mimes:png,jpg,jpeg',
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
            'is_certificate_enabled' => 'nullable|boolean',
            'is_ticket_enabled' => 'nullable|boolean',
            'visibility' => $this->visibilityRule(),
            'certificate_settings' => 'nullable|json',
            'scanner_restriction_mode' => 'nullable|string|in:disabled,exact,radius',
            'scanner_radius_value' => 'nullable|numeric|min:0.001',
            'scanner_radius_unit' => 'nullable|string|in:m,km',
            'scanner_early_minutes' => 'nullable|integer|min:0|max:1440',
            'scanner_late_minutes' => 'nullable|integer|min:0|max:1440',
            'batch_1_price' => 'nullable|numeric|min:0',
            'batch_1_deadline' => 'nullable|date',
            'batch_2_price' => 'nullable|numeric|min:0',
            'batch_2_deadline' => 'nullable|date',
            'batch_3_price' => 'nullable|numeric|min:0',
            'batch_3_deadline' => 'nullable|date',
            'type' => 'nullable|string|in:event,album',
            'slug' => 'nullable|string|unique:events,slug,' . ($event ? $event->id : 'NULL'),
        ]);

        return $this->applyScannerRestrictionPayload($request, $validated);
    }

    private function resolveImageRule(Request $request, ?Event $event = null): string
    {
        // Imagem sempre opcional. Eventos sem imagem ficam como rascunho (published=false)
        // e aparecem como "incompletos" até que a imagem seja adicionada.
        return 'nullable|image|max:5120';
    }

    private function normalizeMoney($value): float
    {
        if (!$value) {
            return 0;
        }

        $value = str_replace(['R$', ' ', '.'], '', (string) $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function loadCalendarSettings(): array
    {
        $defaults = $this->defaultCalendarSettings();

        try {
            $raw = (string) (\App\Models\Setting::get('calendar_settings_json', '') ?? '');
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
                ->map(fn($t) => [
                    'title' => trim((string) ($t['title'] ?? '')),
                    'color' => strtoupper(trim((string) ($t['color'] ?? ''))),
                ])
                ->filter(fn($t) => $t['title'] !== '' && preg_match('/^#[0-9A-F]{6}$/', $t['color']))
                ->values()
                ->all();
            if (empty($settings['templates'])) {
                $settings['templates'] = $defaults['templates'];
            }

            $settings['quick_colors'] = collect($settings['quick_colors'])
                ->map(fn($c) => strtoupper(trim((string) $c)))
                ->filter(fn($c) => preg_match('/^#[0-9A-F]{6}$/', $c))
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

    /**
     * Atualiza configuracoes do calendario (cores, templates, view inicial, etc).
     */
    public function updateCalendarSettings(Request $request)
    {
        $this->ensurePermission('events.edit');

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
            ->map(fn($t) => [
                'title' => trim((string) ($t['title'] ?? '')),
                'color' => strtoupper(trim((string) ($t['color'] ?? ''))),
            ])
            ->filter(fn($t) => $t['title'] !== '' && preg_match('/^#[0-9A-F]{6}$/', $t['color']))
            ->values()
            ->all();

        $quickColors = collect($payload['quick_colors'] ?? [])
            ->map(fn($c) => strtoupper(trim((string) $c)))
            ->filter(fn($c) => preg_match('/^#[0-9A-F]{6}$/', $c))
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

        \App\Models\Setting::updateOrCreate(
            ['key' => 'calendar_settings_json'],
            ['value' => json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'group' => 'calendar']
        );

        return response()->json(['status' => 'success', 'message' => 'Configurações do calendário atualizadas.']);
    }

    private function normalizeHexColor(string $color, string $fallback = '#3B82F6'): string
    {
        $color = strtoupper(trim($color));

        if (preg_match('/^#[0-9A-F]{6}$/', $color)) {
            return $color;
        }

        return $fallback;
    }

    private function resolveContrastColor(string $backgroundColor): string
    {
        $hex = ltrim($backgroundColor, '#');

        if (strlen($hex) !== 6) {
            return '#FFFFFF';
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance >= 160 ? '#0F172A' : '#FFFFFF';
    }

    private function canManageAllEvents(): bool
    {
        $user = Auth::user();

        return $user && $user->isAdmin();
    }

    protected function ensurePermission(string $permission): void
    {
        $user = Auth::user();

        if ($permission === 'events.view' && $user && method_exists($user, 'canManageEventExhibitors') && $user->canManageEventExhibitors()) {
            return;
        }

        if (!$user || (!$user->isAdmin() && !$user->hasPermission($permission))) {
            abort(403);
        }
    }

    protected function ensureCanManage(Event $event): void
    {
        if ($this->canManageAllEvents()) {
            return;
        }

        if ((int) $event->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

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
