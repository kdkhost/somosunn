<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $publicStatuses = ['published', 'paused'];
        $q = trim((string) $request->query('q', ''));

        $coursesQuery = Course::with('creator')
            ->whereIn('status', $publicStatuses);

        if ($q !== '') {
            $coursesQuery->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('short_description', 'like', '%' . $q . '%');
            });
        }

        $courses = $coursesQuery
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $mentorshipsQuery = Mentorship::with('mentor');

        if ($q !== '') {
            $mentorshipsQuery->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $mentorships = $mentorshipsQuery
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        $eventsQuery = Event::query()
            ->with('user')
            ->where('published', true)
            ->where('start_at', '>=', now())
            ->orderBy('start_at', 'asc');

        if ($q !== '') {
            $eventsQuery->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('speaker', 'like', '%' . $q . '%');
            });
        }

        $events = $eventsQuery
            ->limit(12)
            ->get();

        $testimonials = Testimonial::query()
            ->where('status', 'approved')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $mpAccessToken = trim((string) config('payments.mercadopago.access_token'));
        $mpPublicKey = trim((string) config('payments.mercadopago.public_key'));
        $paymentsConfigured = $mpAccessToken !== '' && $mpPublicKey !== '';

        $canSellByUserId = [];

        foreach ($courses->pluck('creator')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
        }
        foreach ($mentorships->pluck('mentor')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
        }
        foreach ($events->pluck('user')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
        }

        return view('marketplace.index', compact(
            'courses',
            'mentorships',
            'events',
            'testimonials',
            'paymentsConfigured',
            'canSellByUserId',
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
