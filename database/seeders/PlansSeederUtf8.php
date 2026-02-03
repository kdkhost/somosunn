<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansSeederUtf8 extends Seeder
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
                'permissions' => [
                    'dashboard.view',
                    'events.view',
                    'courses.view',
                    'mentorships.view',
                    'ranking.view',
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
                'permissions' => [
                    'dashboard.view',
                    'events.view','events.create','events.ticket.manage',
                    'courses.view',
                    'mentorships.view','mentorships.schedule',
                    'ranking.view',
                    'uploads.manage',
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
                'permissions' => [
                    'dashboard.view',
                    'events.view','events.create','events.edit','events.publish','events.ticket.manage',
                    'courses.view',
                    'mentorships.view','mentorships.schedule','mentorships.create',
                    'ranking.view','ranking.edit',
                    'uploads.manage',
                ],
            ],
        ];

        $permIds = DB::table('permissions')->pluck('id','name');

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
                    'created_at'=>$now,
                    'updated_at'=>$now
                ]
            );
            $planRowId = DB::table('plans')->where('slug',$slug)->value('id');
            if(!$planRowId) continue;

            DB::table('permission_plan')->where('plan_id',$planRowId)->delete();
            $rows=[];
            foreach($plan['permissions'] as $pname){
                if(isset($permIds[$pname])){
                    $rows[]=['plan_id'=>$planRowId,'permission_id'=>$permIds[$pname]];
                }
            }
            if($rows){
                DB::table('permission_plan')->insert($rows);
            }
        }
    }
}
