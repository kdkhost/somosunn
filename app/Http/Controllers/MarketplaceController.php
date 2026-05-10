<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\GatewayAccount;
use App\Models\Mentorship;
use App\Models\Order;
use App\Models\SellerProduct;
use App\Models\Testimonial;
use App\Services\Marketplace\SellerStoreService;
use App\Support\ContentVisibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketplaceController extends Controller
{
    public function index(Request $request, SellerStoreService $storeService)
    {
        $publicStatuses = ['published', 'paused'];
        $q = trim((string) $request->query('q', ''));

        $coursesQuery = ContentVisibility::applyPublicFilter(
            Course::with('creator')
                ->whereIn('status', $publicStatuses),
            'courses'
        );

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

        $mentorshipsQuery = ContentVisibility::applyPublicFilter(
            Mentorship::with('mentor'),
            'mentorships'
        );

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

        $eventsQuery = ContentVisibility::applyPublicFilter(
            Event::query()
                ->with('user')
                ->where('published', true),
            'events'
        )
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

        $sellerProducts = collect();
        if (SellerProduct::tableAvailable()) {
            $sellerProductsQuery = SellerProduct::query()
                ->with(['store.user', 'media', 'redeemableItem'])
                ->published()
                ->orderByDesc('is_featured')
                ->latest('id');

            if ($q !== '') {
                $sellerProductsQuery->where(function ($query) use ($q) {
                    $query->where('title', 'like', '%' . $q . '%')
                        ->orWhere('excerpt', 'like', '%' . $q . '%')
                        ->orWhere('description', 'like', '%' . $q . '%');
                });
            }

            $sellerProducts = $sellerProductsQuery
                ->limit(24)
                ->get();
        }

        $testimonials = Testimonial::query()
            ->where('status', 'approved')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $platformGateways = GatewayAccount::resolveForSeller(0);
        $platformGatewaysSumUp = GatewayAccount::resolveForSellerSumUp(0);
        $platformMpEnabled = (bool) ($platformGateways['mpEnabled'] ?? false);
        $platformSumUpEnabled = (bool) ($platformGatewaysSumUp['sumupEnabled'] ?? false);
        $platformPaymentsEnabled = $platformMpEnabled || $platformSumUpEnabled;
        $paymentsConfigured = $platformPaymentsEnabled;

        $canSellByUserId = [];
        $paymentsEnabledByUserId = [];

        $resolveSellerHasGateway = function (int $sellerId) {
            $mp = GatewayAccount::resolveForSeller($sellerId);
            $su = GatewayAccount::resolveForSellerSumUp($sellerId);
            return (bool) ($mp['mpEnabled'] ?? false) || (bool) ($su['sumupEnabled'] ?? false);
        };

        foreach ($courses->pluck('creator')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $paymentsEnabledByUserId[(int) $seller->id] = $resolveSellerHasGateway((int) $seller->id);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }
        foreach ($mentorships->pluck('mentor')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $paymentsEnabledByUserId[(int) $seller->id] = $resolveSellerHasGateway((int) $seller->id);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }
        foreach ($events->pluck('user')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $paymentsEnabledByUserId[(int) $seller->id] = $resolveSellerHasGateway((int) $seller->id);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }

        foreach ($sellerProducts->pluck('store.user')->filter()->unique('id') as $seller) {
            $canSellByUserId[(int) $seller->id] = (bool) $seller->canSellOnMarketplace();
            $paymentsEnabledByUserId[(int) $seller->id] = $resolveSellerHasGateway((int) $seller->id);
            $paymentsConfigured = $paymentsConfigured || $paymentsEnabledByUserId[(int) $seller->id];
        }

        $storeUserIds = collect(array_keys($canSellByUserId));
        $publishedStores = $storeService->publishedStoresByUserIds($storeUserIds);
        $sellerStoreUrlsByUserId = $publishedStores
            ->map(fn($store) => route('seller-stores.show', $store->slug))
            ->all();

        $sellerProducts = $sellerProducts
            ->filter(fn(SellerProduct $product) => isset($sellerStoreUrlsByUserId[(int) $product->user_id]))
            ->take(12)
            ->values();

        return view('marketplace.index', compact(
            'courses',
            'mentorships',
            'events',
            'sellerProducts',
            'testimonials',
            'paymentsConfigured',
            'platformPaymentsEnabled',
            'canSellByUserId',
            'paymentsEnabledByUserId',
            'sellerStoreUrlsByUserId',
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
