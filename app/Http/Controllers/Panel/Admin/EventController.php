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

        $event->forceFill([
            'published' => !$event->published,
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
        $hasCurrentImage = $event && filled($event->image) && !$request->boolean('remove_image');

        return $hasCurrentImage ? 'nullable|image|max:5120' : 'required|image|max:5120';
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
        return [
            'initial_view' => 'dayGridMonth',
            'event_text_color' => '#FFFFFF',
            'button_color' => '#1F5EDB',
        ];
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
}
