<?php

use App\Models\EventRegistration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('event_registrations')
            || !Schema::hasColumn('event_registrations', 'payment_status')
        ) {
            return;
        }

        DB::table('event_registrations')
            ->whereNull('payment_status')
            ->where('status', EventRegistration::STATUS_CANCELLED)
            ->update(['payment_status' => EventRegistration::PAYMENT_CANCELLED]);

        if (Schema::hasColumn('event_registrations', 'order_id') && Schema::hasTable('orders')) {
            DB::table('event_registrations')
                ->join('orders', 'orders.id', '=', 'event_registrations.order_id')
                ->whereNull('event_registrations.payment_status')
                ->whereIn('event_registrations.status', EventRegistration::COUNTED_STATUSES)
                ->where('orders.status', 'paid')
                ->where('orders.gateway', 'free')
                ->update(['event_registrations.payment_status' => EventRegistration::PAYMENT_FREE]);

            DB::table('event_registrations')
                ->join('orders', 'orders.id', '=', 'event_registrations.order_id')
                ->whereNull('event_registrations.payment_status')
                ->whereIn('event_registrations.status', EventRegistration::COUNTED_STATUSES)
                ->where('orders.status', 'paid')
                ->where(function ($query) {
                    $query->whereNull('orders.gateway')
                        ->orWhere('orders.gateway', '!=', 'free');
                })
                ->update(['event_registrations.payment_status' => EventRegistration::PAYMENT_PAID]);
        }

        if (Schema::hasColumn('event_registrations', 'price')) {
            DB::table('event_registrations')
                ->whereNull('payment_status')
                ->whereIn('status', EventRegistration::COUNTED_STATUSES)
                ->where('price', '<=', 0)
                ->update(['payment_status' => EventRegistration::PAYMENT_FREE]);
        }

        DB::table('event_registrations')
            ->whereNull('payment_status')
            ->update(['payment_status' => EventRegistration::PAYMENT_PENDING]);
    }

    public function down(): void
    {
        // Rollback conservador: nao remove nem zera status financeiro ja saneado.
    }
};
