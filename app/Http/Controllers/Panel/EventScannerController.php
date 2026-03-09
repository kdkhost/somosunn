<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\PointsService;
use Illuminate\Http\Request;

class EventScannerController extends Controller
{
    public function index(Event $event)
    {
        if (!auth()->user()->isAdmin() && $event->user_id !== auth()->id()) {
            abort(403, 'Apenas o organizador pode acessar o scanner deste evento.');
        }

        if (!$event->is_ticket_enabled) {
            return redirect()->route('panel.events.show', $event)
                ->with('error', 'A validacao por QR Code nao esta habilitada para este evento.');
        }

        $scannerOpen = $event->isScannerOpen();
        $scannerStatusMessage = $event->scannerStatusMessage();

        return view('panel.events.scanner', compact('event', 'scannerOpen', 'scannerStatusMessage'));
    }

    public function validateTicket(Request $request, Event $event, PointsService $pointsService)
    {
        if (!auth()->user()->isAdmin() && $event->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'ticket_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $ticketCode = $request->input('ticket_code');
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        $now = now();

        if (!$event->isScannerOpen($now)) {
            return response()->json([
                'success' => false,
                'message' => $event->scannerStatusMessage($now),
            ]);
        }

        if ($event->latitude && $event->longitude) {
            if (!$userLat || !$userLng) {
                return response()->json([
                    'success' => false,
                    'message' => 'E necessario permitir o acesso ao GPS para validar neste local.',
                ]);
            }

            $distance = $this->calculateDistance($userLat, $userLng, $event->latitude, $event->longitude);

            if ($distance > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voce nao esta no local configurado para este evento (Distancia: ' . round($distance * 1000) . 'm).',
                ]);
            }
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->with('user')
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
                'message' => 'Este ingresso ja foi validado em ' . $registration->check_in_at->format('H:i'),
            ]);
        }

        $registration->update([
            'check_in_at' => $now,
        ]);

        try {
            if ($registration->user_id) {
                $user = $registration->user;
                if ($user) {
                    $pointsService->award($user, 'event_scan_participant');
                }
            }

            $organizer = \App\Models\User::find($event->user_id);
            if ($organizer) {
                $pointsService->award($organizer, 'event_scan_organizer');
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao atribuir pontos no check-in do evento: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Ingresso validado com sucesso! Check-in realizado.',
            'participant_name' => $registration->user ? $registration->user->name : $registration->name,
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
