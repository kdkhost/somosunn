<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $plan = $user ? $user->activePlan() : null;

        $stats = [
            'courses_count' => 0,
            'orders_paid_count' => 0,
            'orders_paid_total' => 0.0,
            'seller_paid_count' => 0,
            'seller_net_total' => 0.0,
        ];

        try {
            if ($user) {
                $stats['courses_count'] = (int) Enrollment::query()
                    ->where('user_id', $user->id)
                    ->where('enrollable_type', Course::class)
                    ->count();

                $stats['orders_paid_count'] = (int) Order::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->count();

                $stats['orders_paid_total'] = (float) Order::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->sum('total_amount');

                if (method_exists($user, 'canSellOnMarketplace') && $user->canSellOnMarketplace()) {
                    $sellerPaidTotal = (float) Order::query()
                        ->where('seller_id', $user->id)
                        ->where('status', 'paid')
                        ->sum('total_amount');

                    $sellerFeeTotal = (float) Order::query()
                        ->where('seller_id', $user->id)
                        ->where('status', 'paid')
                        ->sum('platform_fee_amount');

                    $stats['seller_paid_count'] = (int) Order::query()
                        ->where('seller_id', $user->id)
                        ->where('status', 'paid')
                        ->count();

                    $stats['seller_net_total'] = (float) max(0, $sellerPaidTotal - $sellerFeeTotal);
                }
            }
        } catch (\Throwable $e) {
            // Dashboard não pode quebrar (fallback silencioso)
        }

        return view('panel.dashboard', compact('user', 'plan', 'stats'));
    }
}

