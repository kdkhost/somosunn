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
        Schema::table('points_rules', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('label');
            $table->string('description')->nullable()->after('category');
            $table->integer('sort_order')->default(0)->after('description');
            $table->string('icon', 50)->nullable()->after('sort_order');
            $table->boolean('repeatable')->default(false)->after('active');
            $table->integer('max_daily')->nullable()->after('repeatable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('points_rules', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'sort_order', 'icon', 'repeatable', 'max_daily']);
        });
    }
};
