<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\PointsService;
use Illuminate\Http\Request;

class QuickScannerController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Event::where('start_at', '>=', now()->startOfDay())
            ->where('start_at', '<=', now()->addDays(3)->endOfDay())
            ->where('published', true)
            ->where('is_ticket_enabled', true);

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $todayEvents = $query->orderBy('start_at')->get();

        return view('panel.admin.quick-scanner', compact('todayEvents'));
    }

    public function validateTicket(Request $request, PointsService $pointsService)
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $ticketCode = $request->input('ticket_code');
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');

        $registration = EventRegistration::where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->with(['event', 'user'])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresso nao encontrado ou invalido.',
            ]);
        }

        $event = $registration->event;
        $authUser = auth()->user();

        if (!$authUser->isAdmin() && $event->user_id !== $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Voce nao tem permissao para validar ingressos deste evento.',
            ]);
        }

        $now = now();
        if (!$event->isScannerOpen($now)) {
            return response()->json([
                'success' => false,
                'message' => $event->scannerStatusMessage($now),
                'event_title' => $event->title,
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

        if ($registration->check_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Este ingresso ja foi validado em ' . $registration->check_in_at->format('H:i'),
                'participant_name' => $registration->user ? $registration->user->name : $registration->name,
                'event_title' => $event->title,
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
            \Log::error('Erro ao atribuir pontos no Quick Scanner: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Entrada Liberada!',
            'participant_name' => $registration->user ? $registration->user->name : $registration->name,
            'event_title' => $event->title,
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
