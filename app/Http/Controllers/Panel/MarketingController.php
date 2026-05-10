<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\OrderSplit;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard do Responsavel de Marketing.
     */
    public function index(Request $request)
    {
        abort_unless($this->userIsMarketingManager(), 403, 'Area exclusiva do Responsavel de Marketing.');

        $user = Auth::user();

        $splits = OrderSplit::query()
            ->where('receiver_type', 'traffic')
            ->where('receiver_id', $user->id)
            ->latest()
            ->limit(200)
            ->get();

        // Totais por status (pagos, pendentes, a receber) - receiver_type=traffic
        $allTraffic = OrderSplit::where('receiver_type', 'traffic')
            ->where('receiver_id', $user->id)
            ->get();

        $metrics = [
            'total_paid'     => (float) $allTraffic->where('status', 'paid')->sum('amount'),
            'total_pending'  => (float) $allTraffic->where('status', 'pending')->sum('amount'),
            'total_rejected' => (float) $allTraffic->where('status', 'rejected')->sum('amount'),
            'total_all'      => (float) $allTraffic->sum('amount'),
            'count_all'      => $allTraffic->count(),
            'count_paid'     => $allTraffic->where('status', 'paid')->count(),
            'count_pending'  => $allTraffic->where('status', 'pending')->count(),
        ];

        $percent = (float) Setting::get('marketplace_split_traffic_percent', 10);

        return view('panel.marketing.index', compact('splits', 'metrics', 'percent'));
    }

    protected function userIsMarketingManager(): bool
    {
        $userId = Auth::id();
        if (!$userId) {
            return false;
        }
        $managerId = (int) Setting::get('platform_marketing_user_id', 0);
        return $managerId > 0 && $managerId === $userId;
    }
}
