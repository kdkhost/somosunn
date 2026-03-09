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
}
