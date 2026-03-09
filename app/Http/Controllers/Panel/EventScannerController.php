<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PointsService;
use App\Services\EventScannerAttemptLogger;
use App\Services\EventTicketScannerService;
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

    public function validateTicket(
        Request $request,
        Event $event,
        PointsService $pointsService,
        EventTicketScannerService $scannerService,
        EventScannerAttemptLogger $attemptLogger
    )
    {
        if (!auth()->user()->isAdmin() && $event->user_id !== auth()->id()) {
            $attemptLogger->log(
                $event,
                null,
                auth()->user(),
                'panel_event',
                false,
                'forbidden',
                'Acesso negado.',
                (string) $request->input('ticket_code', ''),
                $request->input('latitude') !== null ? (float) $request->input('latitude') : null,
                $request->input('longitude') !== null ? (float) $request->input('longitude') : null
            );

            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'ticket_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        return response()->json(
            $scannerService->validateForEvent(
                $event,
                (string) $request->input('ticket_code'),
                $request->input('latitude') !== null ? (float) $request->input('latitude') : null,
                $request->input('longitude') !== null ? (float) $request->input('longitude') : null,
                auth()->user(),
                $pointsService,
                'panel_event'
            )
        );
    }
}
