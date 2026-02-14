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
                $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
                $isSuperadmin = method_exists($user, 'isSuperadmin') && $user->isSuperadmin();
                // Admin/superadmin: visão global
                if ($isAdmin || $isSuperadmin) {
                    $stats['courses_count'] = (int) \App\Models\Course::count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) \App\Models\Order::where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('status', 'paid')->sum('platform_fee_amount');
                } else {
                    // Responsável: só vê seus produtos
                    $stats['courses_count'] = (int) \App\Models\Course::where('user_id', $user->id)->count();
                    $stats['orders_paid_count'] = (int) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->count();
                    $stats['orders_paid_total'] = (float) \App\Models\Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
                    $stats['seller_paid_count'] = (int) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->count();
                    $stats['seller_net_total'] = (float) max(0, (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('total_amount') - (float) \App\Models\Order::where('seller_id', $user->id)->where('status', 'paid')->sum('platform_fee_amount'));
                }
            }
        } catch (\Throwable $e) {
            // Dashboard não pode quebrar (fallback silencioso)
        }

        return view('panel.dashboard', compact('user', 'plan', 'stats'));
    }
}

