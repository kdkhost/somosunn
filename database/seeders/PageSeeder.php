<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug'  => 'home',
                'title' => 'Página Inicial',
                'data'  => [
                    // Hero
                    'hero_title'    => 'Conectando empreendedores.',
                    'hero_subtitle' => 'Criando oportunidades reais.',
                    'body'          => 'A UNN é uma comunidade de networking estratégico onde empreendedores compartilham experiências, constroem conexões e crescem juntos.',
                    'hero_cta_text'  => 'Quero fazer parte',
                    'hero_cta2_text' => 'Conhecer a UNN',
                    // SEO
                    'seo_title'       => 'UNN - Conectando Empreendedores',
                    'seo_description' => 'A UNN é a plataforma de networking para empreendedores que querem crescer juntos.',
                    // Stats
                    'stat_1_value' => '5.000+',
                    'stat_1_label' => 'Empreendedores',
                    'stat_2_value' => 'R$ 50M+',
                    'stat_2_label' => 'Em negócios gerados',
                    'stat_3_value' => '200+',
                    'stat_3_label' => 'Eventos realizados',
                    'stat_4_value' => '27',
                    'stat_4_label' => 'Estados',
                    // Seção Sobre
                    'about_title'    => 'O que é a UNN',
                    'about_subtitle' => 'A UNN nasceu para unir empreendedores que acreditam no crescimento colaborativo.',
                    'about_card_1_title' => 'Conexões reais',
                    'about_card_1_text'  => 'Networking genuíno com empreendedores que compartilham seus valores',
                    'about_card_2_title' => 'Crescimento coletivo',
                    'about_card_2_text'  => 'Juntos somos mais fortes e alcançamos resultados maiores',
                    'about_card_3_title' => 'Troca de experiências',
                    'about_card_3_text'  => 'Aprenda com quem já passou pelos desafios que você enfrenta',
                    'about_card_4_title' => 'Oportunidades',
                    'about_card_4_text'  => 'Parcerias estratégicas que geram resultados concretos',
                    // Seção Eventos
                    'events_title'    => 'Palestras gratuitas',
                    'events_subtitle' => 'Eventos que chegam em breve',
                    // Seção Mentorias
                    'mentorships_title'    => 'Mentorias premium',
                    'mentorships_subtitle' => 'Conteúdo gravado + acompanhamento de mentores',
                    // Seção Comunidade
                    'community_title'         => 'Comunidade por níveis',
                    'community_beginner_title' => 'Empreendedores iniciantes',
                    'community_beginner_desc'  => 'Conectados entre si e acolhidos por quem já percorreu a jornada.',
                    'community_success_title'  => 'Empresários de sucesso',
                    'community_success_desc'   => 'Mentores ativos, parceiros e investidores prontos para novas oportunidades.',
                    // Seção Ranking
                    'ranking_title'    => 'Ranking do networking',
                    'ranking_subtitle' => 'Baseado nas avaliações após cada conexão',
                    // Seção Depoimentos
                    'testimonials_title' => 'O que dizem nossos membros',
                    'testimonials' => [
                        ['name' => 'Carlos Eduardo', 'role' => 'CEO, Tech Solutions', 'text' => 'A UNN transformou minha forma de fazer negócios. Em 6 meses, fechei parcerias que mudaram minha empresa.', 'rating' => 5],
                        ['name' => 'Ana Paula Lima', 'role' => 'Fundadora, EcoModa', 'text' => 'O networking aqui é diferente. São conexões genuínas com pessoas que realmente querem ajudar.', 'rating' => 5],
                        ['name' => 'Roberto Silva', 'role' => 'Investidor Anjo', 'text' => 'Encontrei projetos incríveis para investir e empreendedores talentosos. A comunidade é de altíssimo nível.', 'rating' => 5],
                    ],
                    // CTA Final
                    'cta_section_title'        => 'Pronto para transformar sua rede?',
                    'cta_section_subtitle'     => 'Junte-se a milhares de empreendedores que já estão crescendo juntos.',
                    'cta_section_btn_primary'  => 'Começar agora - É grátis',
                    'cta_section_btn_secondary' => 'Ver planos Premium',
                ],
            ],
            [
                'slug'  => 'sobre',
                'title' => 'Sobre Nós',
                'data'  => [
                    'hero' => [
                        'headline' => 'Sobre a Somos UNN',
                        'body'     => 'Somos uma plataforma criada para conectar pessoas e oportunidades.',
                    ],
                    'seo' => [
                        'title'       => 'Sobre | Somos UNN',
                        'description' => 'Conheça a história e a missão da Somos UNN.',
                    ],
                ],
            ],
            [
                'slug'  => 'manifesto',
                'title' => 'Manifesto',
                'data'  => [
                    'headline' => 'Nosso Manifesto',
                    'body'     => 'Acreditamos que o conhecimento compartilhado é o maior ativo de uma geração.',
                    'seo' => [
                        'title'       => 'Manifesto | Somos UNN',
                        'description' => 'Leia o manifesto da Somos UNN.',
                    ],
                ],
            ],
            [
                'slug'  => 'quem-somos',
                'title' => 'Quem Somos',
                'data'  => [
                    'headline' => 'Quem Somos',
                    'body'     => 'Uma equipe apaixonada por educação, tecnologia e comunidade.',
                    'team'     => [],
                    'seo' => [
                        'title'       => 'Quem Somos | Somos UNN',
                        'description' => 'Conheça o time por trás da Somos UNN.',
                    ],
                ],
            ],
            [
                'slug'  => 'como-funciona',
                'title' => 'Como Funciona',
                'data'  => [
                    'headline' => 'Como Funciona',
                    'steps'    => [
                        ['icon' => '🎓', 'title' => 'Escolha seu plano', 'body' => 'Selecione o plano ideal para você.'],
                        ['icon' => '📚', 'title' => 'Acesse os cursos',  'body' => 'Aprenda no seu ritmo com conteúdo exclusivo.'],
                        ['icon' => '🤝', 'title' => 'Conecte-se',        'body' => 'Faça parte de uma rede de profissionais.'],
                    ],
                    'seo' => [
                        'title'       => 'Como Funciona | Somos UNN',
                        'description' => 'Entenda como a Somos UNN funciona.',
                    ],
                ],
            ],
            [
                'slug'  => 'valores',
                'title' => 'Nossos Valores',
                'data'  => [
                    'headline' => 'Nossos Valores',
                    'items'    => [
                        ['title' => 'Comunidade',  'body' => 'Crescemos juntos, aprendemos juntos.'],
                        ['title' => 'Transparência','body' => 'Clareza em tudo que fazemos.'],
                        ['title' => 'Inovação',     'body' => 'Buscamos sempre formas melhores de ensinar.'],
                        ['title' => 'Impacto',      'body' => 'Cada ação gera transformação real.'],
                    ],
                    'seo' => [
                        'title'       => 'Valores | Somos UNN',
                        'description' => 'Conheça os valores que guiam a Somos UNN.',
                    ],
                ],
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                ['title' => $page['title'], 'data' => $page['data']]
            );
        }
    }
}
