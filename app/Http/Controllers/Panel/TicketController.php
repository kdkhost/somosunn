<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Exibe a listagem de ingressos do membro.
     */
    public function index()
    {
        $user = Auth::user();

        $registrations = EventRegistration::with(['event', 'order'])
            ->where('user_id', $user->id)
            ->whereIn('status', [EventRegistration::STATUS_PAID, EventRegistration::STATUS_CONFIRMED])
            ->latest()
            ->paginate(12);

        return view('panel.tickets', compact('registrations'));
    }

    /**
     * Exibe o ingresso digital com detalhes do evento e QR Code quando habilitado.
     */
    public function show(EventRegistration $registration)
    {
        abort_unless((int) $registration->user_id === (int) Auth::id(), 403);
        abort_unless(in_array((string) $registration->status, EventRegistration::COUNTED_STATUSES, true), 404);

        $registration->load(['event', 'order']);
        abort_unless($registration->event, 404);

        $printRegistrations = EventRegistration::with(['event', 'order'])
            ->where('user_id', Auth::id())
            ->where('event_id', $registration->event_id)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->when(
                $registration->order_id,
                fn ($query) => $query->where('order_id', $registration->order_id),
                fn ($query) => $query->whereKey($registration->getKey())
            )
            ->orderBy('id')
            ->get();

        if ($printRegistrations->isEmpty()) {
            $printRegistrations = collect([$registration]);
        }

        return view('panel.tickets.show', compact('registration', 'printRegistrations'));
    }
}
