<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\PointsService;
use Illuminate\Http\Request;

class EventScannerController extends Controller
{
    public function index(Event $event)
    {
        // Administradores e Superadmins têm acesso irrestrito no painel admin
        // Mas a view precisa existir e o evento precisa ter ingressos habilitados
        if (!$event->is_ticket_enabled) {
            return redirect()->route('admin.events.show', $event)
                ->with('error', 'A validação por QR Code não está habilitada para este evento.');
        }

        return view('admin.events.scanner', compact('event'));
    }

    public function validateTicket(Request $request, Event $event, PointsService $pointsService)
    {
        $request->validate([
            'ticket_code' => 'required|string'
        ]);

        $ticketCode = $request->input('ticket_code');

        // Busca o ingresso
        $registration = EventRegistration::where('event_id', $event->id)
            ->where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->first();

        if (!$registration) {
            return response()->json(['success' => false, 'message' => 'Ingresso não encontrado ou inválido para este evento.']);
        }

        if ($registration->check_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Este ingresso já foi validado em ' . $registration->check_in_at->format('d/m/Y H:i')
            ]);
        }

        // Realiza o check-in
        $registration->update([
            'check_in_at' => now()
        ]);

        // Atribui pontos
        try {
            // Pontos para o participante
            if ($registration->user_id) {
                $user = \App\Models\User::find($registration->user_id);
                if ($user)
                    $pointsService->award($user, 'event_scan_participant');
            }

            // Pontos para o organizador original do evento
            $organizer = \App\Models\User::find($event->user_id);
            if ($organizer)
                $pointsService->award($organizer, 'event_scan_organizer');
        } catch (\Exception $e) {
            \Log::error('Erro ao atribuir pontos no check-in do evento (Admin): ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Ingresso validado com sucesso! Check-in realizado.',
            'participant_name' => $registration->user ? $registration->user->name : $registration->name,
        ]);
    }
}
