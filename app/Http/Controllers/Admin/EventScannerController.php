<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PointsService;
use App\Services\EventTicketScannerService;
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

    public function validateTicket(
        Request $request,
        Event $event,
        PointsService $pointsService,
        EventTicketScannerService $scannerService
    )
    {
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
                'admin_event'
            )
        );
    }
}
