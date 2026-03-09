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
        if (!$event->is_ticket_enabled) {
            return redirect()->route('admin.events.show', $event)
                ->with('error', 'A validacao por QR Code nao esta habilitada para este evento.');
        }

        $scannerOpen = $event->isScannerOpen();
        $scannerStatusMessage = $event->scannerStatusMessage();

        return view('admin.events.scanner', compact('event', 'scannerOpen', 'scannerStatusMessage'));
    }

    public function validateTicket(Request $request, Event $event, PointsService $pointsService)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        $now = now();
        if (!$event->isScannerOpen($now)) {
            return response()->json([
                'success' => false,
                'message' => $event->scannerStatusMessage($now),
            ]);
        }

        $ticketCode = $request->input('ticket_code');

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresso nao encontrado ou invalido para este evento.',
            ]);
        }

        if ($registration->check_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Este ingresso ja foi validado em ' . $registration->check_in_at->format('d/m/Y H:i'),
            ]);
        }

        $registration->update([
            'check_in_at' => $now,
        ]);

        try {
            if ($registration->user_id) {
                $user = \App\Models\User::find($registration->user_id);
                if ($user) {
                    $pointsService->award($user, 'event_scan_participant');
                }
            }

            $organizer = \App\Models\User::find($event->user_id);
            if ($organizer) {
                $pointsService->award($organizer, 'event_scan_organizer');
            }
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
