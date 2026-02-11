<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\GatewayAccount;
use App\Models\Mentorship;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index()
    {
        $publicStatuses = ['published', 'paused'];

        $courses = Course::with('creator')
            ->whereIn('status', $publicStatuses)
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $mentorships = Mentorship::with('mentor')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $events = Event::query()
            ->with('user')
            ->where('published', true)
            ->orderByDesc('start_at')
            ->limit(6)
            ->get();

        $courseSellerIds = $courses->pluck('user_id')->unique()->values()->all();
        $mentorshipSellerIds = $mentorships->pluck('mentor_id')->unique()->values()->all();
        $eventSellerIds = $events->pluck('user_id')->unique()->values()->all();

        $gatewayEnabledUserIds = GatewayAccount::query()
            ->where('enabled', true)
            ->whereIn('user_id', array_values(array_unique(array_merge($courseSellerIds, $mentorshipSellerIds, $eventSellerIds))))
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $canSellByUserId = [];

        foreach ($courses->pluck('creator')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canAccessFeature('marketplace.sell');
        }
        foreach ($mentorships->pluck('mentor')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canAccessFeature('marketplace.sell');
        }
        foreach ($events->pluck('user')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canAccessFeature('marketplace.sell');
        }

        return view('marketplace.index', compact(
            'courses',
            'mentorships',
            'events',
            'gatewayEnabledUserIds',
            'canSellByUserId'
        ));
    }

    public function sales()
    {
        $userId = (int) Auth::id();

        $orders = Order::with(['user:id,name,email', 'items'])
            ->where('seller_id', $userId)
            ->latest('id')
            ->paginate(20);

        $paidTotal = (float) Order::where('seller_id', $userId)->where('status', 'paid')->sum('total_amount');
        $paidCount = (int) Order::where('seller_id', $userId)->where('status', 'paid')->count();

        return view('marketplace.sales', compact('orders', 'paidTotal', 'paidCount'));
    }
}
