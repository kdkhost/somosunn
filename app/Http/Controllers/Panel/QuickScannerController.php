<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PointsService;
use App\Services\EventTicketScannerService;
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

    public function validateTicket(
        Request $request,
        PointsService $pointsService,
        EventTicketScannerService $scannerService
    )
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $result = $scannerService->validateQuickScan(
            (string) $request->input('ticket_code'),
            $request->input('latitude') !== null ? (float) $request->input('latitude') : null,
            $request->input('longitude') !== null ? (float) $request->input('longitude') : null,
            auth()->user(),
            $pointsService,
            'panel_quick'
        );

        if (($result['success'] ?? false) === true) {
            $result['message'] = 'Entrada Liberada!';
        }

        return response()->json($result);
    }
}
