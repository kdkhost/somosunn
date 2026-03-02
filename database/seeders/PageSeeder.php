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
                    'hero' => [
                        'headline'    => 'Bem-vindo à Somos UNN',
                        'subheadline' => 'A comunidade que transforma talentos em carreiras.',
                        'cta_text'    => 'Comece agora',
                        'cta_url'     => '/cadastro',
                    ],
                    'seo' => [
                        'title'       => 'Somos UNN — Plataforma de Cursos e Eventos',
                        'description' => 'Aprenda, conecte-se e evolua com a Somos UNN.',
                    ],
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
