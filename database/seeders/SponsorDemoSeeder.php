<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Sponsor;
use App\Models\SponsorBanner;
use App\Models\SponsorPlan;
use Illuminate\Database\Seeder;

class SponsorDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!Company::tableAvailable() || !Sponsor::tableAvailable() || !SponsorPlan::tableAvailable()) {
            return;
        }

        // Garante que os 8 pacotes existam primeiro
        $this->call(SponsorPlanSeeder::class);

        $plansByDays = SponsorPlan::query()->get()->keyBy('duration_days');
        $fallbackPlan = SponsorPlan::query()->first();

        $demos = [
            [
                'company' => [
                    'slug' => 'demo-techpulse-cloud',
                    'name' => 'TechPulse Cloud (Demo)',
                    'email' => 'contato@techpulse.demo',
                    'website' => 'https://somosunn.com.br',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'description' => 'Infraestrutura em nuvem NVMe e hospedagem escalável para negócios digitais.',
                    'verified' => true,
                    'active' => true,
                ],
                'duration_days' => 365,
                'banner' => [
                    'title' => 'TechPulse Cloud — Servidores NVMe com 20% OFF',
                    'image' => 'uploads/sponsors/banners/demo-techpulse.svg',
                    'url' => 'https://somosunn.com.br',
                    'position' => 'global_banner',
                    'approval_status' => SponsorBanner::STATUS_APPROVED,
                    'impressions_count' => 14820,
                    'clicks_count' => 612,
                ],
            ],
            [
                'company' => [
                    'slug' => 'demo-vertice-financeira',
                    'name' => 'Vértice Financeira (Demo)',
                    'email' => 'comercial@vertice.demo',
                    'website' => 'https://somosunn.com.br',
                    'city' => 'Curitiba',
                    'state' => 'PR',
                    'description' => 'Consultoria e BPO financeiro completo para empresas em crescimento.',
                    'verified' => true,
                    'active' => true,
                ],
                'duration_days' => 90,
                'banner' => [
                    'title' => 'Vértice Financeira — Diagnóstico Gratuito BPO',
                    'image' => 'uploads/sponsors/banners/demo-vertice.svg',
                    'url' => 'https://somosunn.com.br',
                    'position' => 'community_feed',
                    'approval_status' => SponsorBanner::STATUS_APPROVED,
                    'impressions_count' => 8450,
                    'clicks_count' => 389,
                ],
            ],
            [
                'company' => [
                    'slug' => 'demo-lumina-academy',
                    'name' => 'Lumina Academy (Demo)',
                    'email' => 'parcerias@lumina.demo',
                    'website' => 'https://somosunn.com.br',
                    'city' => 'Belo Horizonte',
                    'state' => 'MG',
                    'description' => 'Escola executiva de liderança, inteligência artificial e gestão estratégica.',
                    'verified' => true,
                    'active' => true,
                ],
                'duration_days' => 120,
                'banner' => [
                    'title' => 'Lumina Academy — Formação Executiva em IA',
                    'image' => 'uploads/sponsors/banners/demo-lumina.svg',
                    'url' => 'https://somosunn.com.br',
                    'position' => 'global_banner',
                    'approval_status' => SponsorBanner::STATUS_APPROVED,
                    'impressions_count' => 6190,
                    'clicks_count' => 274,
                ],
            ],
            [
                'company' => [
                    'slug' => 'demo-norte-studio',
                    'name' => 'Norte Studio Digital (Demo)',
                    'email' => 'ola@nortestudio.demo',
                    'website' => 'https://somosunn.com.br',
                    'city' => 'Florianópolis',
                    'state' => 'SC',
                    'description' => 'Agência especializada em branding, produção audiovisual e performance.',
                    'verified' => true,
                    'active' => true,
                ],
                'duration_days' => 30,
                'banner' => [
                    'title' => 'Norte Studio — Branding & Tráfego Pago',
                    'image' => 'uploads/sponsors/banners/demo-norte.svg',
                    'url' => 'https://somosunn.com.br',
                    'position' => 'community_feed',
                    'approval_status' => SponsorBanner::STATUS_PENDING,
                    'impressions_count' => 0,
                    'clicks_count' => 0,
                ],
            ],
        ];

        foreach ($demos as $item) {
            $company = Company::updateOrCreate(
                ['slug' => $item['company']['slug']],
                $item['company']
            );

            $plan = $plansByDays->get($item['duration_days']) ?? $fallbackPlan;
            $days = max(1, (int) ($plan?->duration_days ?? $item['duration_days']));

            $sponsor = Sponsor::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'sponsor_plan_id' => $plan?->id,
                    'starts_at' => now()->subDays(2),
                    'ends_at' => now()->addDays($days),
                    'status' => Sponsor::STATUS_ACTIVE,
                ]
            );

            if (SponsorBanner::tableAvailable()) {
                SponsorBanner::updateOrCreate(
                    [
                        'sponsor_id' => $sponsor->id,
                        'title' => $item['banner']['title'],
                    ],
                    array_merge($item['banner'], [
                        'sponsor_plan_id' => $plan?->id,
                        'starts_at' => now()->subDays(2),
                        'ends_at' => now()->addDays($days),
                        'active' => true,
                    ])
                );
            }
        }
    }
}
