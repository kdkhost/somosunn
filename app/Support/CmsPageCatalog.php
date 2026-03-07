<?php

namespace App\Support;

use App\Models\Page;

class CmsPageCatalog
{
    /**
     * @return array<int, array{slug:string,title:string,data:array<string,mixed>}>
     */
    public static function definitions(): array
    {
        return [
            [
                'slug' => 'home',
                'title' => 'Página Inicial',
                'data' => [
                    'hero_title' => 'Conectando empreendedores.',
                    'hero_subtitle' => 'Criando oportunidades reais.',
                    'body' => 'A UNN é uma comunidade de networking estratégico onde empreendedores compartilham experiências, constroem conexões e crescem juntos.',
                    'hero_cta_text' => 'Quero fazer parte',
                    'hero_cta2_text' => 'Conhecer a UNN',
                    'seo_title' => 'UNN - Conectando Empreendedores',
                    'seo_description' => 'A UNN é a plataforma de networking para empreendedores que querem crescer juntos.',
                    'stat_1_value' => '5.000+',
                    'stat_1_label' => 'Empreendedores',
                    'stat_2_value' => 'R$ 50M+',
                    'stat_2_label' => 'Em negócios gerados',
                    'stat_3_value' => '200+',
                    'stat_3_label' => 'Eventos realizados',
                    'stat_4_value' => '27',
                    'stat_4_label' => 'Estados',
                    'about_title' => 'O que é a UNN',
                    'about_subtitle' => 'A UNN nasceu para unir empreendedores que acreditam no crescimento colaborativo.',
                    'about_card_1_title' => 'Conexões reais',
                    'about_card_1_text' => 'Networking genuíno com empreendedores que compartilham seus valores',
                    'about_card_2_title' => 'Crescimento coletivo',
                    'about_card_2_text' => 'Juntos somos mais fortes e alcançamos resultados maiores',
                    'about_card_3_title' => 'Troca de experiências',
                    'about_card_3_text' => 'Aprenda com quem já passou pelos desafios que você enfrenta',
                    'about_card_4_title' => 'Oportunidades',
                    'about_card_4_text' => 'Parcerias estratégicas que geram resultados concretos',
                    'events_title' => 'Palestras gratuitas',
                    'events_subtitle' => 'Eventos que chegam em breve',
                    'mentorships_title' => 'Mentorias premium',
                    'mentorships_subtitle' => 'Conteúdo gravado + acompanhamento de mentores',
                    'community_title' => 'Comunidade por níveis',
                    'community_beginner_title' => 'Empreendedores iniciantes',
                    'community_beginner_desc' => 'Conectados entre si e acolhidos por quem já percorreu a jornada.',
                    'community_success_title' => 'Empresários de sucesso',
                    'community_success_desc' => 'Mentores ativos, parceiros e investidores prontos para novas oportunidades.',
                    'ranking_title' => 'Ranking do networking',
                    'ranking_subtitle' => 'Baseado nas avaliações após cada conexão',
                    'testimonials_title' => 'O que dizem nossos membros',
                    'testimonials' => [
                        [
                            'name' => 'Carlos Eduardo',
                            'role' => 'CEO, Tech Solutions',
                            'text' => 'A UNN transformou minha forma de fazer negócios. Em 6 meses, fechei parcerias que mudaram minha empresa.',
                            'rating' => 5,
                        ],
                        [
                            'name' => 'Ana Paula Lima',
                            'role' => 'Fundadora, EcoModa',
                            'text' => 'O networking aqui é diferente. São conexões genuínas com pessoas que realmente querem ajudar.',
                            'rating' => 5,
                        ],
                        [
                            'name' => 'Roberto Silva',
                            'role' => 'Investidor Anjo',
                            'text' => 'Encontrei projetos incríveis para investir e empreendedores talentosos. A comunidade é de altíssimo nível.',
                            'rating' => 5,
                        ],
                    ],
                    'cta_section_title' => 'Pronto para transformar sua rede?',
                    'cta_section_subtitle' => 'Junte-se a milhares de empreendedores que já estão crescendo juntos.',
                    'cta_section_btn_primary' => 'Começar agora - É grátis',
                    'cta_section_btn_secondary' => 'Ver planos Premium',
                ],
            ],
            [
                'slug' => 'sobre',
                'title' => 'Sobre Nós',
                'data' => [
                    'seo_title' => 'Sobre Nós - UNN',
                    'hero_title' => 'Somos a ponte entre quem quer crescer e quem já chegou lá.',
                    'vision' => 'A UNN nasceu da crença de que empreendedores crescem mais rápido quando constroem conexões reais. Não somos apenas uma plataforma - somos uma comunidade viva de pessoas que se ajudam, indicam e crescem juntas.',
                    'cta_btn_primary' => 'Fazer parte',
                    'cta_btn_secondary' => 'Conhecer a equipe',
                    'stat_1_value' => '5.000+',
                    'stat_1_label' => 'Membros ativos',
                    'stat_2_value' => 'R$ 50M+',
                    'stat_2_label' => 'Em negócios gerados',
                    'stat_3_value' => '200+',
                    'stat_3_label' => 'Eventos realizados',
                    'stat_4_value' => '27',
                    'stat_4_label' => 'Estados',
                    'history_title' => 'Nossa História',
                    'history_lead' => 'Em 2020, um grupo de empreendedores frustrados com a superficialidade do networking tradicional decidiu criar algo diferente.',
                    'history_p1' => 'A UNN nasceu de uma conversa franca entre pessoas que acreditavam que as conexões certas podiam mudar trajetórias de vida. Começamos pequenos, com encontros mensais em São Paulo, e rapidamente percebemos que estávamos tocando em algo essencial: a necessidade humana de pertencer a uma tribo de iguais.',
                    'history_p2' => 'Hoje, a UNN está presente em todo o Brasil, com membros de todos os setores e tamanhos de empresas. Mas o espírito continua o mesmo: conexões reais, conversas verdadeiras e parcerias que mudam vidas.',
                    'diff_title' => 'Por que a UNN é diferente',
                    'diff_card_1_title' => 'Curadoria de membros',
                    'diff_card_1_text' => 'Nosso processo seletivo garante que você estará em contato com empreendedores sérios e comprometidos com o crescimento mútuo.',
                    'diff_card_2_title' => 'Formato estruturado',
                    'diff_card_2_text' => 'Nossos eventos seguem uma metodologia comprovada que maximiza conexões relevantes em menos tempo.',
                    'diff_card_3_title' => 'Resultado mensurável',
                    'diff_card_3_text' => 'Membros reportam em média 3 novas parcerias nos primeiros 90 dias de participação ativa.',
                    'cta_title' => 'Faça parte da nossa história',
                    'cta_subtitle' => 'Junte-se a milhares de empreendedores que já transformaram seus negócios através da UNN.',
                    'cta_btn' => 'Quero fazer parte',
                ],
            ],
            [
                'slug' => 'manifesto',
                'title' => 'Manifesto',
                'data' => [
                    'seo_title' => 'Manifesto - UNN',
                    'hero_title' => 'Acreditamos no poder',
                    'hero_title_highlight' => 'das conexões humanas.',
                    'hero_subtitle' => 'Esse é o nosso manifesto. Leia com o coração.',
                    'quote_top' => 'Nenhum empreendedor chega longe sozinho. Por trás de todo grande negócio, existe uma rede de pessoas que acreditaram, apoiaram e abriram portas.',
                    'section_1_title' => 'Acreditamos que o networking é uma habilidade.',
                    'section_1_text' => 'Não um talento inato ou um dom de poucos. É algo que pode ser desenvolvido, praticado e aperfeiçoado. E quando bem feito, transforma trajetórias inteiras.',
                    'section_2_title' => 'Acreditamos na generosidade estratégica.',
                    'section_2_text' => 'A melhor conexão começa quando você pergunta "como posso ajudar?" antes de "o que você pode fazer por mim?". Dar sem esperar retorno imediato é o investimento mais inteligente que um empreendedor pode fazer.',
                    'section_3_title' => 'Acreditamos que comunidade bate competição.',
                    'section_3_text' => 'No novo mundo dos negócios, colaborar é mais poderoso que competir. Os empreendedores que entenderam isso são os que estão crescendo mais rápido.',
                    'section_4_title' => 'Acreditamos em conversas reais.',
                    'section_4_text' => 'Chega de networking superficial, troca de cartões que nunca viram negócios, e eventos onde ninguém se lembra de você no dia seguinte. Queremos conversas que importam.',
                    'section_5_title' => 'Acreditamos que o melhor está à frente.',
                    'section_5_text' => 'A UNN existe para provar que quando empreendedores certos se encontram, acontece algo extraordinário. E estamos apenas começando.',
                    'quote_bottom' => 'A UNN não é apenas uma plataforma. É um movimento. E você é parte dele.',
                    'quote_author' => '- Equipe Fundadora UNN',
                    'pillars_title' => 'Os pilares que nos guiam',
                    'pillar_1_title' => 'Confiança',
                    'pillar_2_title' => 'Generosidade',
                    'pillar_3_title' => 'Inovação',
                    'pillar_4_title' => 'Impacto',
                    'pillars_link_text' => 'Conheça todos os nossos valores ->',
                    'cta_title' => 'Você compartilha desses valores?',
                    'cta_subtitle' => 'Então você está no lugar certo. Faça parte da nossa comunidade.',
                    'cta_btn' => 'Quero fazer parte',
                ],
            ],
            [
                'slug' => 'quem-somos',
                'title' => 'Quem Somos',
                'data' => [
                    'seo_title' => 'Quem Somos - Equipe UNN',
                    'hero_subtitle' => 'Conheça as pessoas por trás da maior comunidade de networking do Brasil.',
                    'founders_title' => 'Fundadores',
                    'founders' => [
                        [
                            'name' => 'Ricardo Andrade',
                            'role' => 'CEO & Co-Fundador',
                            'bio' => 'Empreendedor serial com exits em 3 startups. Acredita no poder transformador das conexões humanas.',
                            'initials' => 'RA',
                        ],
                        [
                            'name' => 'Patrícia Lima',
                            'role' => 'COO & Co-Fundadora',
                            'bio' => 'Especialista em operações e escalabilidade. Ex-executiva de grandes corporações.',
                            'initials' => 'PL',
                        ],
                        [
                            'name' => 'Marcos Teixeira',
                            'role' => 'CTO & Co-Fundador',
                            'bio' => 'Engenheiro de software com 20 anos de experiência. Apaixonado por tecnologia e inovação.',
                            'initials' => 'MT',
                        ],
                    ],
                    'team_title' => 'Nossa Equipe',
                    'team' => [
                        ['name' => 'Camila Rocha', 'role' => 'Head de Comunidade', 'initials' => 'CR'],
                        ['name' => 'Bruno Dias', 'role' => 'Head de Eventos', 'initials' => 'BD'],
                        ['name' => 'Larissa Costa', 'role' => 'Head de Marketing', 'initials' => 'LC'],
                        ['name' => 'Gabriel Santos', 'role' => 'Head de Parcerias', 'initials' => 'GS'],
                        ['name' => 'Fernanda Alves', 'role' => 'Head de Conteúdo', 'initials' => 'FA'],
                        ['name' => 'Lucas Pereira', 'role' => 'Head de Tecnologia', 'initials' => 'LP'],
                    ],
                    'stats_title' => 'UNN em Números',
                    'stat_1_value' => '15',
                    'stat_1_label' => 'Colaboradores',
                    'stat_2_value' => '4',
                    'stat_2_label' => 'Anos de história',
                    'stat_3_value' => '5k+',
                    'stat_3_label' => 'Membros atendidos',
                    'stat_4_value' => '100%',
                    'stat_4_label' => 'Dedicação',
                    'cta_title' => 'Quer fazer parte do time?',
                    'cta_subtitle' => 'Estamos sempre em busca de talentos que compartilham nossa visão.',
                    'cta_btn' => 'Entre em contato',
                ],
            ],
            [
                'slug' => 'como-funciona',
                'title' => 'Como Funciona',
                'data' => [
                    'seo_title' => 'Como Funciona - UNN',
                    'hero_subtitle' => 'Entenda como a UNN pode transformar sua rede de contatos e impulsionar seus negócios.',
                    'steps' => [
                        [
                            'direction' => 'row',
                            'title' => 'Cadastre-se na Plataforma',
                            'text' => 'Crie sua conta gratuitamente e preencha seu perfil completo. Quanto mais informações você compartilhar, melhores serão as conexões que a plataforma irá sugerir para você.',
                            'li' => [
                                'Cadastro rápido em menos de 2 minutos',
                                'Perfil personalizado com suas especialidades',
                                'Integração com LinkedIn',
                            ],
                        ],
                        [
                            'direction' => 'row-reverse',
                            'title' => 'Conecte-se com Outros Membros',
                            'text' => 'Navegue pela comunidade, encontre empreendedores com interesses similares e inicie conversas. Nossa plataforma facilita o primeiro contato e incentiva conexões genuínas.',
                            'li' => [
                                'Sistema de match inteligente',
                                'Chat integrado na plataforma',
                                'Grupos temáticos por setor',
                            ],
                        ],
                        [
                            'direction' => 'row',
                            'title' => 'Participe de Eventos',
                            'text' => 'Compareça aos nossos eventos presenciais e online. Networking acontece de verdade quando olhamos nos olhos um do outro. Nossos eventos são cuidadosamente planejados para maximizar conexões.',
                            'li' => [
                                'Eventos presenciais em todo Brasil',
                                'Webinars semanais com especialistas',
                                'Mentorias em grupo',
                            ],
                        ],
                        [
                            'direction' => 'row-reverse',
                            'title' => 'Feche Negócios',
                            'text' => 'Transforme conexões em parcerias e negócios reais. Membros da UNN já geraram mais de R$ 50 milhões em negócios entre si. Sua próxima grande oportunidade pode estar a uma conexão de distância.',
                            'li' => [
                                'Sistema de indicações entre membros',
                                'Acompanhamento de deals fechados',
                                'Cases de sucesso da comunidade',
                            ],
                        ],
                    ],
                    'plans_title' => 'Escolha seu Plano',
                    'plans_subtitle' => 'Temos opções para todos os estágios da sua jornada empreendedora.',
                    'cta_title' => 'Pronto para começar?',
                    'cta_subtitle' => 'Crie sua conta agora e comece a fazer conexões valiosas.',
                    'cta_btn' => 'Criar conta grátis',
                ],
            ],
            [
                'slug' => 'valores',
                'title' => 'Nossos Valores',
                'data' => [
                    'seo_title' => 'Nossos Valores - UNN',
                    'hero_subtitle' => 'Os princípios que guiam tudo o que fazemos na UNN.',
                    'values' => [
                        [
                            'icon' => 'fa-heart',
                            'title' => 'Confiança',
                            'text' => 'A base de qualquer relacionamento duradouro. Cultivamos um ambiente onde a palavra tem valor e os compromissos são honrados. Confiança não se exige, se constrói.',
                            'quote' => 'Confiança é a cola invisível que mantém as parcerias unidas.',
                        ],
                        [
                            'icon' => 'fa-hands-helping',
                            'title' => 'Generosidade',
                            'text' => 'O verdadeiro networking começa quando você se pergunta: "Como posso ajudar?". Acreditamos que dar sem esperar nada em troca cria as conexões mais poderosas.',
                            'quote' => 'Quem planta conexões, colhe oportunidades.',
                        ],
                        [
                            'icon' => 'fa-lightbulb',
                            'title' => 'Inovação',
                            'text' => 'Nunca paramos de evoluir. Buscamos constantemente novas formas de conectar pessoas e gerar valor. A zona de conforto não é lugar para empreendedores.',
                            'quote' => 'Inovar é ver o que todos veem e pensar o que ninguém pensou.',
                        ],
                        [
                            'icon' => 'fa-trophy',
                            'title' => 'Excelência',
                            'text' => 'Fazemos o nosso melhor em tudo. Cada evento, cada interação, cada detalhe é pensado para proporcionar a melhor experiência possível aos nossos membros.',
                            'quote' => 'Excelência não é um ato, é um hábito.',
                        ],
                        [
                            'icon' => 'fa-user-shield',
                            'title' => 'Integridade',
                            'text' => 'Fazemos o que é certo, mesmo quando ninguém está olhando. A ética nos negócios não é opcional, é fundamental. Nossos membros são selecionados por seu caráter.',
                            'quote' => 'O caráter se revela nas pequenas decisões do dia a dia.',
                        ],
                        [
                            'icon' => 'fa-users',
                            'title' => 'Comunidade',
                            'text' => 'Somos mais fortes juntos. A UNN não é apenas uma plataforma, é uma família de empreendedores que se apoiam mutuamente nos desafios e celebram as vitórias um do outro.',
                            'quote' => 'Sozinhos vamos mais rápido. Juntos vamos mais longe.',
                        ],
                    ],
                    'blockquote_text' => 'Valores não são apenas palavras bonitas na parede. São os critérios pelos quais tomamos cada decisão, grandes ou pequenas, todos os dias.',
                    'blockquote_author' => '- Equipe Fundadora UNN',
                    'cta_title' => 'Compartilha desses valores?',
                    'cta_subtitle' => 'Você está no lugar certo. Faça parte da nossa comunidade.',
                    'cta_btn' => 'Fazer parte',
                ],
            ],
            [
                'slug' => 'eventos',
                'title' => 'Eventos',
                'data' => [
                    'seo_title' => 'Próximos Eventos - UNN',
                    'seo_description' => 'Confira os próximos eventos UNN e não perca a chance de expandir sua rede.',
                    'hero_title' => 'Próximo Evento UNN',
                    'hero_subtitle' => 'Não perca a oportunidade de expandir sua rede',
                ],
            ],
            [
                'slug' => 'membros',
                'title' => 'Membros',
                'data' => [
                    'seo_title' => 'Membros - UNN',
                    'seo_description' => 'Conheça os empreendedores da comunidade UNN.',
                    'hero_title' => 'Membros UNN',
                    'hero_subtitle' => 'Conheça os empreendedores que fazem parte da nossa comunidade exclusiva de networking empresarial.',
                ],
            ],
            [
                'slug' => 'vagas-abertas',
                'title' => 'Oportunidades de Carreira',
                'data' => [
                    'seo_title' => 'Oportunidades de Carreira - UNN',
                    'seo_description' => 'Encontre vagas e oportunidades exclusivas para impulsionar sua carreira.',
                    'hero_title' => 'Descubra seu próximo passo na UNN Startups',
                    'hero_subtitle' => 'Conecte-se com empresas inovadoras, aplique para vagas exclusivas e acelere sua trajetória profissional.',
                ],
            ],
            [
                'slug' => 'cursos',
                'title' => 'Academy - Cursos',
                'data' => [
                    'seo_title' => 'Academy - SOMOS UNN',
                    'seo_description' => 'Domine as habilidades que transformam mercados com os cursos da UNN Academy.',
                    'hero_title' => 'A Maestria dos Negócios Começa Aqui.',
                    'hero_subtitle' => 'Domine as habilidades que transformam mercados. Conteúdo prático para quem não aceita o comum.',
                ],
            ],
            [
                'slug' => 'portal',
                'title' => 'Portal de Networking',
                'data' => [
                    'seo_title' => 'Portal de Networking - UNN',
                    'seo_description' => 'Acesse palestras, mentorias premium e recursos exclusivos para seu crescimento.',
                    'hero_title' => 'Portal de Networking',
                    'hero_subtitle' => 'Acesse palestras, mentorias premium e recursos exclusivos para potencializar seu crescimento empreendedor.',
                ],
            ],
            [
                'slug' => 'premium',
                'title' => 'Planos Premium',
                'data' => [
                    'seo_title' => 'Planos Premium - UNN',
                    'seo_description' => 'Conheça os planos premium da UNN e eleve seu networking a outro nível.',
                    'hero_title' => 'Eleve seu networking a outro nível',
                    'hero_subtitle' => 'Escolha o plano ideal para potencializar suas conexões e oportunidades.',
                    'plans_title' => 'Escolha seu plano',
                    'plans_subtitle' => 'Invista no seu crescimento',
                ],
            ],
            [
                'slug' => 'feed',
                'title' => 'Comunidade - Feed',
                'data' => [
                    'seo_title' => 'Comunidade - UNN',
                    'seo_description' => 'Conecte-se com outros empreendedores, compartilhe experiências e cresça junto na comunidade UNN.',
                ],
            ],
        ];
    }

    public static function createMissing(): void
    {
        foreach (self::definitions() as $page) {
            Page::query()->firstOrCreate(
                ['slug' => $page['slug']],
                ['title' => $page['title'], 'data' => $page['data']]
            );
        }
    }

    public static function upsertDefaults(): void
    {
        foreach (self::definitions() as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                ['title' => $page['title'], 'data' => $page['data']]
            );
        }
    }
}
