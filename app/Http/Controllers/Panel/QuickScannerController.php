<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QuickScannerController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Pega eventos de hoje e próximos dias
        $query = Event::where('start_at', '>=', now()->startOfDay())
            ->where('start_at', '<=', now()->addDays(3)->endOfDay())
            ->where('published', true)
            ->where('is_ticket_enabled', true);

        // Se não for admin, vê apenas os seus
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
            'longitude' => 'nullable|numeric'
        ]);

        $ticketCode = $request->input('ticket_code');
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');

        // Busca o ingresso em qualquer evento ativo
        $registration = EventRegistration::where('ticket_code', $ticketCode)
            ->whereIn('status', EventRegistration::COUNTED_STATUSES)
            ->with(['event', 'user'])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresso não encontrado ou inválido.'
            ]);
        }

        $event = $registration->event;
        $authUser = auth()->user();

        // 0. Verificação de Permissão (Instrutores só validam seus próprios eventos)
        if (!$authUser->isAdmin() && $event->user_id !== $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para validar ingressos deste evento.'
            ]);
        }

        $now = now();

        // 1. Validação de Data (Apenas no dia do evento)
        $eventDay = Carbon::parse($event->start_at)->startOfDay();
        if (!$now->isSameDay($eventDay)) {
            return response()->json([
                'success' => false,
                'message' => 'Este evento não é hoje (Agendado para: ' . $eventDay->format('d/m/Y') . ').'
            ]);
        }

        // 2. Validação de Horário (Não permitir se o evento já terminou a mais de 4 horas)
        if ($event->end_at) {
            $endLimit = Carbon::parse($event->end_at)->addHours(4);
            if ($now->gt($endLimit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este evento já foi encerrado.'
                ]);
            }
        }

        // 3. Validação de GPS (Obrigatória se o evento tiver coordenadas)
        if ($event->latitude && $event->longitude) {
            if (!$userLat || !$userLng) {
                return response()->json([
                    'success' => false,
                    'message' => 'É necessário permitir o acesso ao GPS para validar neste local.'
                ]);
            }

            $distance = $this->calculateDistance($userLat, $userLng, $event->latitude, $event->longitude);

            // Tolerância de 10 metros (0.01 km)
            if ($distance > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está no local configurado para este evento (Distância: ' . round($distance * 1000) . 'm).'
                ]);
            }
        }

        // 4. Verifica se o ingresso já foi usado
        if ($registration->check_in_at) {
            return response()->json([
                'success' => false,
                'message' => 'Este ingresso já foi validado em ' . $registration->check_in_at->format('H:i'),
                'participant_name' => $registration->user ? $registration->user->name : $registration->name,
                'event_title' => $event->title
            ]);
        }

        // Realiza o check-in
        $registration->update([
            'check_in_at' => $now
        ]);

        // Atribui pontos
        try {
            if ($registration->user_id) {
                $user = $registration->user;
                if ($user)
                    $pointsService->award($user, 'event_scan_participant');
            }

            $organizerId = $event->user_id;
            $organizer = \App\Models\User::find($organizerId);
            if ($organizer)
                $pointsService->award($organizer, 'event_scan_organizer');
        } catch (\Exception $e) {
            \Log::error('Erro ao atribuir pontos no Quick Scanner: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Entrada Liberada!',
            'participant_name' => $registration->user ? $registration->user->name : $registration->name,
            'event_title' => $event->title
        ]);
    }

    /**
     * Calcula distância em KM usando a fórmula de Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
