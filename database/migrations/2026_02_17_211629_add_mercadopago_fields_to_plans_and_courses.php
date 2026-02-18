<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('mp_plan_id')->nullable()->after('price');
            $table->boolean('is_recurring')->default(true)->after('mp_plan_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('mp_plan_id')->nullable()->after('price');
            $table->boolean('is_recurring')->default(false)->after('mp_plan_id'); // Cursos são avulsos por padrão
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['mp_plan_id', 'is_recurring']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['mp_plan_id', 'is_recurring']);
        });
    }
};
