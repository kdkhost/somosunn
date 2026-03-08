<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\GatewayAccount;
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
            ->whereIn('status', $publicStatuses)
            ->where(function ($query) {
                $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
            });

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

        $mentorshipsQuery = Mentorship::with('mentor')
            ->where(function ($query) {
                $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
            });

        if ($q !== '') {
            $mentorshipsQuery->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $mentorships = $mentorshipsQuery
            ->orderByDesc('id')
            ->limit(36)
            ->get()
            ->filter(fn(Mentorship $mentorship) => $mentorship->hasPublicAction())
            ->take(12)
            ->values();

        $eventsQuery = Event::query()
            ->with('user')
            ->where('published', true)
            ->where(function ($query) {
                $query->where('visibility', '!=', 'somos_unicas')->orWhereNull('visibility');
            })
            ->publicUpcoming()
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
        $psToken = trim((string) config('payments.pagseguro.token'));
        $paymentsConfigured = ($mpAccessToken !== '' && $mpPublicKey !== '') || $psToken !== '';

        $canSellByUserId = [];
        $paymentsEnabledByUserId = [];

        foreach ($courses->pluck('creator')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $gateways = GatewayAccount::resolveForSeller((int) $seller->id);
            $paymentsEnabledByUserId[(int) $seller->id] = (bool) ($gateways['mpEnabled'] || $gateways['psEnabled']);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }
        foreach ($mentorships->pluck('mentor')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $gateways = GatewayAccount::resolveForSeller((int) $seller->id);
            $paymentsEnabledByUserId[(int) $seller->id] = (bool) ($gateways['mpEnabled'] || $gateways['psEnabled']);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }
        foreach ($events->pluck('user')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $gateways = GatewayAccount::resolveForSeller((int) $seller->id);
            $paymentsEnabledByUserId[(int) $seller->id] = (bool) ($gateways['mpEnabled'] || $gateways['psEnabled']);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }

        return view('marketplace.index', compact(
            'courses',
            'mentorships',
            'events',
            'testimonials',
            'paymentsConfigured',
            'canSellByUserId',
            'paymentsEnabledByUserId',
        ));
    }

    public function sales()
    {
        $userId = (int) Auth::id();

        $orders = Order::with(['user:id,name,email', 'items'])
            ->where('seller_id', $userId)
            ->latest('id')
            ->paginate(20);

        $paidTotal = (float) Order::where('seller_id', $userId)->financialPaid()->sum('total_amount');
        $paidCount = (int) Order::where('seller_id', $userId)->financialPaid()->count();

        return view('marketplace.sales', compact('orders', 'paidTotal', 'paidCount'));
    }
}
