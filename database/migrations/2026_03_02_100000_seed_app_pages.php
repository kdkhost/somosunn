<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pages = [
            [
                'slug'  => 'eventos',
                'title' => 'Eventos',
                'data'  => json_encode([
                    'seo_title'       => 'Próximos Eventos - UNN',
                    'seo_description' => 'Confira os próximos eventos UNN e não perca a chance de expandir sua rede.',
                    'hero_title'      => 'Próximo Evento UNN',
                    'hero_subtitle'   => 'Não perca a oportunidade de expandir sua rede',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug'  => 'membros',
                'title' => 'Membros',
                'data'  => json_encode([
                    'seo_title'       => 'Membros - UNN',
                    'seo_description' => 'Conheça os empreendedores da comunidade UNN.',
                    'hero_title'      => 'Membros UNN',
                    'hero_subtitle'   => 'Conheça os empreendedores que fazem parte da nossa comunidade exclusiva de networking empresarial.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug'  => 'vagas-abertas',
                'title' => 'Oportunidades de Carreira',
                'data'  => json_encode([
                    'seo_title'       => 'Oportunidades de Carreira - UNN',
                    'seo_description' => 'Encontre vagas e oportunidades exclusivas para impulsionar sua carreira.',
                    'hero_title'      => 'Descubra seu próximo passo na UNN Startups',
                    'hero_subtitle'   => 'Conecte-se com empresas inovadoras, aplique para vagas exclusivas e acelere sua trajetória profissional.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug'  => 'cursos',
                'title' => 'Academy - Cursos',
                'data'  => json_encode([
                    'seo_title'       => 'Academy - SOMOS UNN',
                    'seo_description' => 'Domine as habilidades que transformam mercados com os cursos da UNN Academy.',
                    'hero_title'      => 'A Maestria dos Negócios Começa Aqui.',
                    'hero_subtitle'   => 'Domine as habilidades que transformam mercados. Conteúdo prático para quem não aceita o comum.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug'  => 'portal',
                'title' => 'Portal de Networking',
                'data'  => json_encode([
                    'seo_title'       => 'Portal de Networking - UNN',
                    'seo_description' => 'Acesse palestras, mentorias premium e recursos exclusivos para seu crescimento.',
                    'hero_title'      => 'Portal de Networking',
                    'hero_subtitle'   => 'Acesse palestras, mentorias premium e recursos exclusivos para potencializar seu crescimento empreendedor.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug'  => 'premium',
                'title' => 'Planos Premium',
                'data'  => json_encode([
                    'seo_title'       => 'Planos Premium - UNN',
                    'seo_description' => 'Conheça os planos premium da UNN e eleve seu networking a outro nível.',
                    'hero_title'      => 'Eleve seu networking a outro nível',
                    'hero_subtitle'   => 'Escolha o plano ideal para potencializar suas conexões e oportunidades.',
                    'plans_title'     => 'Escolha seu plano',
                    'plans_subtitle'  => 'Invista no seu crescimento',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug'  => 'feed',
                'title' => 'Comunidade - Feed',
                'data'  => json_encode([
                    'seo_title'       => 'Comunidade - UNN',
                    'seo_description' => 'Conecte-se com outros empreendedores, compartilhe experiências e cresça junto na comunidade UNN.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->insertOrIgnore($page);
        }
    }

    public function down(): void
    {
        DB::table('pages')->whereIn('slug', [
            'eventos', 'membros', 'vagas-abertas', 'cursos', 'portal', 'premium', 'feed',
        ])->delete();
    }
};
