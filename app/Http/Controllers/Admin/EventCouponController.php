<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventCouponController extends Controller
{
    protected string $viewPrefix = 'admin.events.coupons';
    protected string $routePrefix = 'admin.events.coupons';
    protected string $eventsRoutePrefix = 'admin.events';

    public function index(Event $event)
    {
        $this->ensureAccess($event, 'admin.events.coupons.view');

        $coupons = $event->coupons()
            ->with('creator:id,name')
            ->latest('id')
            ->paginate(20);

        return view($this->viewPrefix . '.index', [
            'event' => $event,
            'coupons' => $coupons,
            'routePrefix' => $this->routePrefix,
            'eventsRoutePrefix' => $this->eventsRoutePrefix,
        ]);
    }

    public function create(Event $event)
    {
        $this->ensureAccess($event, 'admin.events.coupons.create');

        return view($this->viewPrefix . '.form', [
            'event' => $event,
            'coupon' => new EventCoupon(['type' => EventCoupon::TYPE_FREE, 'discount_value' => 100, 'active' => true]),
            'routePrefix' => $this->routePrefix,
            'eventsRoutePrefix' => $this->eventsRoutePrefix,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $this->ensureAccess($event, 'admin.events.coupons.create');

        $data = $this->validatedData($request, $event);
        $data['event_id'] = (int) $event->id;
        $data['created_by'] = Auth::id();

        $coupon = new EventCoupon($data);
        $coupon->normalizeCode();
        $coupon->save();

        return $this->redirectOrJson($request, 'Cupom criado com sucesso.');
    }

    public function edit(Event $event, EventCoupon $coupon)
    {
        $this->ensureCouponBelongsToEvent($event, $coupon);
        $this->ensureAccess($event, 'admin.events.coupons.edit');

        return view($this->viewPrefix . '.form', [
            'event' => $event,
            'coupon' => $coupon,
            'routePrefix' => $this->routePrefix,
            'eventsRoutePrefix' => $this->eventsRoutePrefix,
        ]);
    }

    public function update(Request $request, Event $event, EventCoupon $coupon)
    {
        $this->ensureCouponBelongsToEvent($event, $coupon);
        $this->ensureAccess($event, 'admin.events.coupons.edit');

        $coupon->fill($this->validatedData($request, $event, $coupon));
        $coupon->normalizeCode();
        $coupon->save();

        return $this->redirectOrJson($request, 'Cupom atualizado com sucesso.');
    }

    public function toggle(Event $event, EventCoupon $coupon)
    {
        $this->ensureCouponBelongsToEvent($event, $coupon);
        $this->ensureAccess($event, 'admin.events.coupons.toggle');

        $coupon->forceFill(['active' => !$coupon->active])->save();

        $message = $coupon->active ? 'Cupom ativado.' : 'Cupom desativado.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'success',
                'message' => $message,
                'active' => (bool) $coupon->active,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Event $event, EventCoupon $coupon)
    {
        $this->ensureCouponBelongsToEvent($event, $coupon);
        $this->ensureAccess($event, 'admin.events.coupons.delete');

        $coupon->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cupom removido.']);
        }

        return redirect()->route($this->routePrefix . '.index', $event)->with('success', 'Cupom removido.');
    }

    protected function validatedData(Request $request, Event $event, ?EventCoupon $coupon = null): array
    {
        if ($request->has('code')) {
            $request->merge(['code' => EventCoupon::normalizeCodeValue($request->input('code'))]);
        }

        if ($request->has('discount_value')) {
            $request->merge(['discount_value' => $this->normalizeMoney($request->input('discount_value'))]);
        }

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('event_coupons', 'code')
                    ->where(fn ($query) => $query->where('event_id', (int) $event->id))
                    ->ignore($coupon?->id),
            ],
            'type' => ['required', Rule::in([EventCoupon::TYPE_FREE, EventCoupon::TYPE_PERCENT, EventCoupon::TYPE_FIXED])],
            'discount_value' => 'nullable|numeric|min:0|max:999999.99',
            'max_uses' => 'nullable|integer|min:1|max:1000000',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['discount_value'] = (float) ($data['type'] === EventCoupon::TYPE_FREE ? 100 : ($data['discount_value'] ?? 0));

        return $data;
    }

    protected function normalizeMoney($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(['R$', ' ', "\xc2\xa0"], '', $value);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    protected function redirectOrJson(Request $request, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route($this->routePrefix . '.index', $request->route('event')),
            ]);
        }

        return redirect()->route($this->routePrefix . '.index', $request->route('event'))->with('success', $message);
    }

    protected function ensureAccess(Event $event, string $permission): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->isAdmin()) {
            return;
        }

        abort_unless($user->hasPermission($permission) && (int) $event->user_id === (int) $user->id, 403);
    }

    protected function ensureCouponBelongsToEvent(Event $event, EventCoupon $coupon): void
    {
        abort_unless((int) $coupon->event_id === (int) $event->id, 404);
    }
}
