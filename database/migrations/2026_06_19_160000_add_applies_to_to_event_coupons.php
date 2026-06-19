<?php

use App\Models\EventCoupon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_coupons') || Schema::hasColumn('event_coupons', 'applies_to')) {
            return;
        }

        Schema::table('event_coupons', function (Blueprint $table) {
            $table->string('applies_to', 20)
                ->default(EventCoupon::APPLIES_ATTENDEE)
                ->after('type');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_coupons') || !Schema::hasColumn('event_coupons', 'applies_to')) {
            return;
        }

        Schema::table('event_coupons', function (Blueprint $table) {
            $table->dropColumn('applies_to');
        });
    }
};
