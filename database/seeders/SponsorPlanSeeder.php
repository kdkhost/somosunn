<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SponsorPlanSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('sponsor_plans')) {
            return;
        }

        $plans = [
            ['name' => 'Bronze', 'price' => 490.00, 'max_banners' => 1, 'max_events' => 1, 'max_leads' => 25, 'priority' => 10, 'active' => true],
            ['name' => 'Prata', 'price' => 990.00, 'max_banners' => 2, 'max_events' => 2, 'max_leads' => 80, 'priority' => 20, 'active' => true],
            ['name' => 'Ouro', 'price' => 1990.00, 'max_banners' => 4, 'max_events' => 4, 'max_leads' => 200, 'priority' => 30, 'active' => true],
            ['name' => 'Diamante', 'price' => 4990.00, 'max_banners' => 8, 'max_events' => 12, 'max_leads' => 1000, 'priority' => 40, 'active' => true],
        ];

        foreach ($plans as $plan) {
            DB::table('sponsor_plans')->updateOrInsert(
                ['name' => $plan['name']],
                array_merge($plan, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
