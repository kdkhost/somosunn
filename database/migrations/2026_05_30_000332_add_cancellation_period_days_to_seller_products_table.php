<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seller_products', function (Blueprint $table) {
            $table->integer('cancellation_period_days')->default(0)->after('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_products', function (Blueprint $table) {
            $table->dropColumn('cancellation_period_days');
        });
    }
};
