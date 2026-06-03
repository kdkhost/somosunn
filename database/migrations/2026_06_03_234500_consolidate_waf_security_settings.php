<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('waf_settings')) {
            return;
        }

        $legacyKeys = [
            'monitor' => 'waf.threshold.monitor',
            'challenge' => 'waf.threshold.challenge',
            'block' => 'waf.threshold.block',
        ];

        $thresholds = json_decode((string) DB::table('waf_settings')->where('key', 'waf.thresholds')->value('value'), true);
        $thresholds = is_array($thresholds) ? $thresholds : ['monitor' => 20, 'challenge' => 50, 'block' => 80];

        foreach ($legacyKeys as $name => $key) {
            $legacy = DB::table('waf_settings')->where('key', $key)->value('value');
            if ($legacy !== null) {
                $thresholds[$name] = (int) json_decode((string) $legacy, true);
            }
        }

        DB::table('waf_settings')->updateOrInsert(
            ['key' => 'waf.thresholds'],
            ['value' => json_encode($thresholds), 'updated_at' => now()]
        );

        DB::table('waf_settings')->whereIn('key', array_values($legacyKeys))->delete();
    }

    public function down(): void
    {
    }
};
