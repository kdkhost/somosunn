<?php

namespace Database\Seeders;

use App\Models\SponsorPlan;
use Illuminate\Database\Seeder;

class SponsorPlanSeeder extends Seeder
{
    public function run(): void
    {
        if (!SponsorPlan::tableAvailable()) {
            return;
        }

        $plans = [
            [
                'name' => 'Impulso Semanal (7 dias)',
                'price' => 79.90,
                'duration_days' => 7,
                'max_banners' => 1,
                'max_events' => 0,
                'max_leads' => 20,
                'priority' => 10,
                'active' => true,
                'description' => 'Ideal para divulgação rápida de ofertas, lançamentos ou eventos pontuais durante 7 dias com rastreamento de cliques e impressões.',
            ],
            [
                'name' => 'Destaque Quinzenal (14 dias)',
                'price' => 139.90,
                'duration_days' => 14,
                'max_banners' => 1,
                'max_events' => 0,
                'max_leads' => 45,
                'priority' => 20,
                'active' => true,
                'description' => 'Presença contínua por 14 dias nas áreas públicas e no feed da comunidade com excelente custo-benefício.',
            ],
            [
                'name' => 'Campanha 21 Dias',
                'price' => 189.90,
                'duration_days' => 21,
                'max_banners' => 2,
                'max_events' => 1,
                'max_leads' => 75,
                'priority' => 30,
                'active' => true,
                'description' => 'Campanha intermediária de 21 dias com até 2 banners simultâneos e presença em 1 evento da plataforma.',
            ],
            [
                'name' => 'Plano Mensal Pro (30 dias)',
                'price' => 249.90,
                'duration_days' => 30,
                'max_banners' => 2,
                'max_events' => 1,
                'max_leads' => 120,
                'priority' => 40,
                'active' => true,
                'description' => 'Nosso pacote mais procurado: 30 dias completos de exposição no banner global e no feed da comunidade com relatório de CTR.',
            ],
            [
                'name' => 'Bimestral Business (60 dias)',
                'price' => 449.90,
                'duration_days' => 60,
                'max_banners' => 3,
                'max_events' => 2,
                'max_leads' => 250,
                'priority' => 50,
                'active' => true,
                'description' => '60 dias de visibilidade com desconto progressivo, até 3 criativos em rotação e presença em 2 eventos.',
            ],
            [
                'name' => 'Trimestral Premium (90 dias)',
                'price' => 599.90,
                'duration_days' => 90,
                'max_banners' => 4,
                'max_events' => 3,
                'max_leads' => 400,
                'priority' => 60,
                'active' => true,
                'description' => 'Posicionamento de marca sólido por 90 dias com alta prioridade de exibição, 4 banners e captação ampliada de leads.',
            ],
            [
                'name' => 'Quadrimestral Master (120 dias)',
                'price' => 749.90,
                'duration_days' => 120,
                'max_banners' => 5,
                'max_events' => 4,
                'max_leads' => 600,
                'priority' => 70,
                'active' => true,
                'description' => '4 meses completos de campanha contínua com 5 banners ativos, presença em eventos e maior prioridade de rotação.',
            ],
            [
                'name' => 'Patrocinador Anual VIP (365 dias)',
                'price' => 1890.00,
                'duration_days' => 365,
                'max_banners' => 10,
                'max_events' => 12,
                'max_leads' => 2000,
                'priority' => 100,
                'active' => true,
                'description' => 'Cota anual exclusiva com máxima prioridade na plataforma, até 10 banners, presença em todos os meses de eventos e máxima geração de leads.',
            ],
        ];

        foreach ($plans as $plan) {
            SponsorPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
