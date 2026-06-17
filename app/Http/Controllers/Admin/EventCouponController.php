<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventCouponRequest;
use App\Models\Event;
use App\Models\EventCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function store(EventCouponRequest $request, Event $event)
    {
        $this->ensureAccess($event, 'admin.events.coupons.create');

        $data = $request->validatedPayload();
        $data['event_id'] = (int) $event->id;
        $data['created_by'] = Auth::id();

        $coupon = new EventCoupon($data);
        $coupon->normalizeCode();
        $coupon->save();

        Log::info('Cupom de evento criado', [
            'event_id' => $event->id,
            'coupon_id' => $coupon->id,
            'created_by' => Auth::id(),
        ]);

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

    public function update(EventCouponRequest $request, Event $event, EventCoupon $coupon)
    {
        $this->ensureCouponBelongsToEvent($event, $coupon);
        $this->ensureAccess($event, 'admin.events.coupons.edit');

        $coupon->fill($request->validatedPayload());
        $coupon->normalizeCode();
        $coupon->save();

        Log::info('Cupom de evento atualizado', [
            'event_id' => $event->id,
            'coupon_id' => $coupon->id,
            'updated_by' => Auth::id(),
        ]);

        return $this->redirectOrJson($request, 'Cupom atualizado com sucesso.');
    }

    public function toggle(Event $event, EventCoupon $coupon)
    {
        $this->ensureCouponBelongsToEvent($event, $coupon);
        $this->ensureAccess($event, 'admin.events.coupons.toggle');

        $coupon->forceFill(['active' => !$coupon->active])->save();

        $message = $coupon->active ? 'Cupom ativado.' : 'Cupom desativado.';

        Log::info('Status de cupom de evento alterado', [
            'event_id' => $event->id,
            'coupon_id' => $coupon->id,
            'active' => (bool) $coupon->active,
            'updated_by' => Auth::id(),
        ]);

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

        if ((int) $coupon->used_count > 0 || $coupon->registrations()->exists()) {
            $message = 'Este cupom já foi usado e não pode ser removido. Desative o cupom para impedir novos usos.';

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $coupon->delete();

        Log::info('Cupom de evento removido', [
            'event_id' => $event->id,
            'coupon_id' => $coupon->id,
            'deleted_by' => Auth::id(),
        ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cupom removido.']);
        }

        return redirect()->route($this->routePrefix . '.index', $event)->with('success', 'Cupom removido.');
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
