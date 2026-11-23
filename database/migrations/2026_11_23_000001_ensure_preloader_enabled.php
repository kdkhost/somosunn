<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::set('preloader_enabled', '1');
        Setting::flushRuntimeCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não reverte para evitar quebrar o layout
    }
};
