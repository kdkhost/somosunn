<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $plans = [
            'starter' => [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Entrada na comunidade: eventos gratuitos, cursos básicos e acesso ao ranking.',
                'price' => 0,
                'is_featured' => 0,
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'features' => [
                    'community',
                    'courses',
                    'events',
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Intermediário: tudo do Starter + mentorias em grupo, eventos com prioridade e cursos completos.',
                'price' => 49.90,
                'is_featured' => 1,
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'features' => [
                    'community',
                    'chat',
                    'courses',
                    'events',
                    'mentorships',
                ],
            ],
            'elite' => [
                'name' => 'Elite',
                'slug' => 'elite',
                'description' => 'Executivo: tudo do Pro + mentorias 1:1, eventos VIP e prioridade máxima.',
                'price' => 149.90,
                'is_featured' => 0,
                'billing_cycle' => 'monthly',
                'prorata' => false,
                'features' => [
                    'community',
                    'chat',
                    'courses',
                    'events',
                    'mentorships',
                ],
            ],
        ];

        foreach($plans as $slug => $plan){
            DB::table('plans')->updateOrInsert(
                ['slug'=>$slug],
                [
                    'name'=>$plan['name'],
                    'slug'=>$plan['slug'],
                    'price'=>$plan['price'],
                    'description'=>$plan['description'],
                    'is_featured'=>$plan['is_featured'],
                    'billing_cycle'=>$plan['billing_cycle'],
                    'prorata'=>$plan['prorata'],
                    'permissions'=>json_encode(Plan::normalizeCommercialPermissions(
                        $plan['features'] ?? [],
                        ((float) ($plan['price'] ?? 0)) <= 0,
                        (float) ($plan['price'] ?? 0)
                    )),
                    'created_at'=>$now,
                    'updated_at'=>$now
                ]
            );
        }
    }
}
