<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('site_contents')) {
            return;
        }

        $hasType = Schema::hasColumn('site_contents', 'type');
        $now = now();

        $sobreHistoryBody = <<<'HTML'
<p>A UNN nasceu em 2020 com uma missão clara: democratizar o acesso ao networking de qualidade no Brasil.</p>
<p>Fundada por um grupo de empreendedores que acreditavam no poder das conexões humanas, a União Nacional de Networking começou como pequenos encontros presenciais em São Paulo. Em poucos meses, a comunidade cresceu exponencialmente, alcançando empreendedores em todos os estados brasileiros.</p>
<p>Hoje, somos a maior plataforma de networking empresarial do país, com milhares de membros ativos que geram negócios, parcerias e amizades duradouras através da nossa metodologia exclusiva de conexões.</p>
HTML;

        $manifestoBody = <<<'HTML'
<h2>Sobre Colaboração</h2>
<p>Em um mundo que celebra o individualismo, nós escolhemos o caminho da colaboração. Sabemos que os maiores negócios nascem de parcerias sólidas, construídas sobre confiança e propósito compartilhado.</p>
<h2>Sobre Abundância</h2>
<p>Rejeitamos a mentalidade de escassez. Há espaço para todos crescerem. Quando um membro prospera, a comunidade inteira se fortalece. O sucesso do outro não é ameaça — é inspiração.</p>
<h2>Sobre Autenticidade</h2>
<p>Valorizamos pessoas reais, com histórias reais. Aqui não há espaço para máscaras ou personagens. As conexões mais poderosas nascem quando nos mostramos vulneráveis e autênticos.</p>
<h2>Sobre Impacto</h2>
<p>Não buscamos apenas lucro. Acreditamos que empreendedores têm o poder de transformar a sociedade. Cada negócio bem-sucedido gera empregos, melhora vidas e inspira outros a seguirem o mesmo caminho.</p>
<h2>Nossa Promessa</h2>
<p>Prometemos criar o ambiente ideal para que você encontre as pessoas certas, no momento certo. Prometemos ser facilitadores de conexões genuínas que geram valor real. Prometemos nunca perder a essência do que nos fez começar: a crença inabalável no poder das pessoas.</p>
HTML;

        $pages = [
            'institucional_sobre' => [
                ['key' => 'title', 'value' => 'Sobre a UNN - União Nacional de Networking', 'type' => 'text'],
                ['key' => 'meta_title', 'value' => 'Sobre a UNN - União Nacional de Networking', 'type' => 'text'],
                ['key' => 'meta_description', 'value' => 'Conheça a União Nacional de Networking (UNN) e entenda como conectamos empreendedores para crescerem juntos através de parcerias estratégicas e negócios colaborativos.', 'type' => 'text'],
                ['key' => 'meta_robots', 'value' => 'index,follow', 'type' => 'text'],
                ['key' => 'og_type', 'value' => 'website', 'type' => 'text'],
                ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text'],

                ['key' => 'hero_title', 'value' => 'Conheça a', 'type' => 'text'],
                ['key' => 'hero_title_highlight', 'value' => 'UNN', 'type' => 'text'],
                ['key' => 'hero_subtitle', 'value' => 'A União Nacional de Networking é a maior comunidade de empreendedores do Brasil, conectando pessoas que querem crescer juntas através de parcerias estratégicas e negócios colaborativos.', 'type' => 'text'],
                ['key' => 'hero_primary_button_text', 'value' => 'Fazer parte', 'type' => 'text'],
                ['key' => 'hero_primary_button_url', 'value' => '/register', 'type' => 'text'],
                ['key' => 'hero_secondary_button_text', 'value' => 'Nosso Manifesto', 'type' => 'text'],
                ['key' => 'hero_secondary_button_url', 'value' => '/manifesto', 'type' => 'text'],
                [
                    'key' => 'hero_stats',
                    'value' => json_encode([
                        ['value' => '5k+', 'label' => 'Empreendedores'],
                        ['value' => '27', 'label' => 'Estados'],
                        ['value' => 'R$ 50M+', 'label' => 'Negócios gerados'],
                        ['value' => '200+', 'label' => 'Eventos realizados'],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'history_title', 'value' => 'Nossa História', 'type' => 'text'],
                ['key' => 'history_body', 'value' => $sobreHistoryBody, 'type' => 'html'],

                ['key' => 'diff_title', 'value' => 'O que nos diferencia', 'type' => 'text'],
                [
                    'key' => 'diff_items',
                    'value' => json_encode([
                        [
                            'icon' => 'fas fa-users',
                            'title' => 'Comunidade Selecionada',
                            'text' => 'Todos os membros passam por uma curadoria para garantir a qualidade das conexões.',
                        ],
                        [
                            'icon' => 'fas fa-handshake',
                            'title' => 'Conexões Reais',
                            'text' => 'Eventos presenciais e online que geram relacionamentos genuínos e duradouros.',
                        ],
                        [
                            'icon' => 'fas fa-chart-line',
                            'title' => 'Resultados Mensuráveis',
                            'text' => 'Acompanhamos e celebramos cada negócio fechado entre nossos membros.',
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'cta_title', 'value' => 'Pronto para crescer com a gente?', 'type' => 'text'],
                ['key' => 'cta_subtitle', 'value' => 'Junte-se a milhares de empreendedores que já transformaram suas carreiras.', 'type' => 'text'],
                ['key' => 'cta_button_text', 'value' => 'Começar agora', 'type' => 'text'],
                ['key' => 'cta_button_url', 'value' => '/register', 'type' => 'text'],
            ],
            'institucional_manifesto' => [
                ['key' => 'title', 'value' => 'Manifesto UNN - Nossa Visão', 'type' => 'text'],
                ['key' => 'meta_title', 'value' => 'Manifesto UNN - Nossa Visão', 'type' => 'text'],
                ['key' => 'meta_description', 'value' => 'Conheça o Manifesto da UNN: o que acreditamos, por que existimos e os pilares que guiam nossa comunidade.', 'type' => 'text'],
                ['key' => 'meta_robots', 'value' => 'index,follow', 'type' => 'text'],
                ['key' => 'og_type', 'value' => 'website', 'type' => 'text'],
                ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text'],

                ['key' => 'hero_title', 'value' => 'Nosso', 'type' => 'text'],
                ['key' => 'hero_title_highlight', 'value' => 'Manifesto', 'type' => 'text'],
                ['key' => 'hero_subtitle', 'value' => 'O que acreditamos e por que existimos.', 'type' => 'text'],

                ['key' => 'manifesto_quote', 'value' => '"Acreditamos que ninguém cresce sozinho."', 'type' => 'text'],
                ['key' => 'manifesto_body', 'value' => $manifestoBody, 'type' => 'html'],
                ['key' => 'highlight_quote', 'value' => '"Sozinhos vamos mais rápido. Juntos vamos mais longe."', 'type' => 'text'],
                ['key' => 'highlight_author', 'value' => '— Filosofia UNN', 'type' => 'text'],

                ['key' => 'pillars_title', 'value' => 'Nossos Pilares', 'type' => 'text'],
                [
                    'key' => 'pillars_items',
                    'value' => json_encode([
                        ['icon' => 'fas fa-heart', 'title' => 'Confiança'],
                        ['icon' => 'fas fa-hands-helping', 'title' => 'Generosidade'],
                        ['icon' => 'fas fa-lightbulb', 'title' => 'Inovação'],
                        ['icon' => 'fas fa-trophy', 'title' => 'Excelência'],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],
                ['key' => 'pillars_button_text', 'value' => 'Conhecer nossos valores', 'type' => 'text'],
                ['key' => 'pillars_button_url', 'value' => '/valores', 'type' => 'text'],

                ['key' => 'cta_title', 'value' => 'Se identificou com nossa visão?', 'type' => 'text'],
                ['key' => 'cta_subtitle', 'value' => 'Faça parte de uma comunidade que pensa como você.', 'type' => 'text'],
                ['key' => 'cta_button_text', 'value' => 'Quero fazer parte', 'type' => 'text'],
                ['key' => 'cta_button_url', 'value' => '/register', 'type' => 'text'],
            ],
            'institucional_quem_somos' => [
                ['key' => 'title', 'value' => 'Quem Somos - Equipe UNN', 'type' => 'text'],
                ['key' => 'meta_title', 'value' => 'Quem Somos - Equipe UNN', 'type' => 'text'],
                ['key' => 'meta_description', 'value' => 'Conheça as pessoas por trás da maior comunidade de networking do Brasil.', 'type' => 'text'],
                ['key' => 'meta_robots', 'value' => 'index,follow', 'type' => 'text'],
                ['key' => 'og_type', 'value' => 'website', 'type' => 'text'],
                ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text'],

                ['key' => 'hero_title_highlight', 'value' => 'Quem', 'type' => 'text'],
                ['key' => 'hero_title', 'value' => 'Somos', 'type' => 'text'],
                ['key' => 'hero_subtitle', 'value' => 'Conheça as pessoas por trás da maior comunidade de networking do Brasil.', 'type' => 'text'],

                ['key' => 'founders_title', 'value' => 'Fundadores', 'type' => 'text'],
                [
                    'key' => 'founders_items',
                    'value' => json_encode([
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
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'team_title', 'value' => 'Nossa Equipe', 'type' => 'text'],
                [
                    'key' => 'team_items',
                    'value' => json_encode([
                        ['name' => 'Camila Rocha', 'role' => 'Head de Comunidade', 'initials' => 'CR'],
                        ['name' => 'Bruno Dias', 'role' => 'Head de Eventos', 'initials' => 'BD'],
                        ['name' => 'Larissa Costa', 'role' => 'Head de Marketing', 'initials' => 'LC'],
                        ['name' => 'Gabriel Santos', 'role' => 'Head de Parcerias', 'initials' => 'GS'],
                        ['name' => 'Fernanda Alves', 'role' => 'Head de Conteúdo', 'initials' => 'FA'],
                        ['name' => 'Lucas Pereira', 'role' => 'Head de Tecnologia', 'initials' => 'LP'],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'numbers_title', 'value' => 'UNN em Números', 'type' => 'text'],
                [
                    'key' => 'numbers_items',
                    'value' => json_encode([
                        ['value' => '15', 'label' => 'Colaboradores'],
                        ['value' => '4', 'label' => 'Anos de história'],
                        ['value' => '5k+', 'label' => 'Membros atendidos'],
                        ['value' => '100%', 'label' => 'Dedicação'],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'cta_title', 'value' => 'Quer fazer parte do time?', 'type' => 'text'],
                ['key' => 'cta_subtitle', 'value' => 'Estamos sempre em busca de talentos que compartilham nossa visão.', 'type' => 'text'],
                ['key' => 'cta_button_text', 'value' => 'Entre em contato', 'type' => 'text'],
                ['key' => 'cta_button_url', 'value' => '/contato', 'type' => 'text'],
            ],
            'institucional_como_funciona' => [
                ['key' => 'title', 'value' => 'Como Funciona - UNN', 'type' => 'text'],
                ['key' => 'meta_title', 'value' => 'Como Funciona - UNN', 'type' => 'text'],
                ['key' => 'meta_description', 'value' => 'Entenda como a UNN pode transformar sua rede de contatos e impulsionar seus negócios.', 'type' => 'text'],
                ['key' => 'meta_robots', 'value' => 'index,follow', 'type' => 'text'],
                ['key' => 'og_type', 'value' => 'website', 'type' => 'text'],
                ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text'],

                ['key' => 'hero_title_highlight', 'value' => 'Como', 'type' => 'text'],
                ['key' => 'hero_title', 'value' => 'Funciona', 'type' => 'text'],
                ['key' => 'hero_subtitle', 'value' => 'Entenda como a UNN pode transformar sua rede de contatos e impulsionar seus negócios.', 'type' => 'text'],

                [
                    'key' => 'steps_items',
                    'value' => json_encode([
                        [
                            'title' => 'Cadastre-se na Plataforma',
                            'text' => 'Crie sua conta gratuitamente e preencha seu perfil completo. Quanto mais informações você compartilhar, melhores serão as conexões que a plataforma irá sugerir para você.',
                            'bullet_1' => 'Cadastro rápido em menos de 2 minutos',
                            'bullet_2' => 'Perfil personalizado com suas especialidades',
                            'bullet_3' => 'Integração com LinkedIn',
                        ],
                        [
                            'title' => 'Conecte-se com Outros Membros',
                            'text' => 'Navegue pela comunidade, encontre empreendedores com interesses similares e inicie conversas. Nossa plataforma facilita o primeiro contato e incentiva conexões genuínas.',
                            'bullet_1' => 'Sistema de match inteligente',
                            'bullet_2' => 'Chat integrado na plataforma',
                            'bullet_3' => 'Grupos temáticos por setor',
                        ],
                        [
                            'title' => 'Participe de Eventos',
                            'text' => 'Compareça aos nossos eventos presenciais e online. Networking acontece de verdade quando olhamos nos olhos um do outro. Nossos eventos são cuidadosamente planejados para maximizar conexões.',
                            'bullet_1' => 'Eventos presenciais em todo Brasil',
                            'bullet_2' => 'Webinars semanais com especialistas',
                            'bullet_3' => 'Mentorias em grupo',
                        ],
                        [
                            'title' => 'Feche Negócios',
                            'text' => 'Transforme conexões em parcerias e negócios reais. Membros da UNN já geraram mais de R$ 50 milhões em negócios entre si. Sua próxima grande oportunidade pode estar a uma conexão de distância.',
                            'bullet_1' => 'Sistema de indicações entre membros',
                            'bullet_2' => 'Acompanhamento de deals fechados',
                            'bullet_3' => 'Cases de sucesso da comunidade',
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'plans_title', 'value' => 'Escolha seu Plano', 'type' => 'text'],
                ['key' => 'plans_subtitle', 'value' => 'Temos opções para todos os estágios da sua jornada empreendedora.', 'type' => 'text'],
                [
                    'key' => 'plans_items',
                    'value' => json_encode([
                        [
                            'title' => 'Gratuito',
                            'price' => 'R$ 0',
                            'period' => '',
                            'tagline' => 'Para começar',
                            'feature_1' => 'Perfil na comunidade',
                            'feature_2' => 'Feed social',
                            'feature_3' => '5 conexões/mês',
                            'feature_4' => '',
                            'button_text' => 'Começar grátis',
                            'button_url' => '/register',
                            'featured' => 0,
                            'badge' => '',
                        ],
                        [
                            'title' => 'Premium',
                            'price' => 'R$ 97',
                            'period' => '/mês',
                            'tagline' => 'Para crescer',
                            'feature_1' => 'Tudo do Gratuito',
                            'feature_2' => 'Conexões ilimitadas',
                            'feature_3' => 'Eventos exclusivos',
                            'feature_4' => 'Cursos e mentorias',
                            'button_text' => 'Assinar Premium',
                            'button_url' => '/premium',
                            'featured' => 1,
                            'badge' => 'POPULAR',
                        ],
                        [
                            'title' => 'Business',
                            'price' => 'R$ 297',
                            'period' => '/mês',
                            'tagline' => 'Para empresas',
                            'feature_1' => 'Tudo do Premium',
                            'feature_2' => '5 usuários inclusos',
                            'feature_3' => 'Consultoria mensal',
                            'feature_4' => 'Suporte prioritário',
                            'button_text' => 'Falar com vendas',
                            'button_url' => '/contato',
                            'featured' => 0,
                            'badge' => '',
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'cta_title', 'value' => 'Pronto para começar?', 'type' => 'text'],
                ['key' => 'cta_subtitle', 'value' => 'Crie sua conta agora e comece a fazer conexões valiosas.', 'type' => 'text'],
                ['key' => 'cta_button_text', 'value' => 'Criar conta grátis', 'type' => 'text'],
                ['key' => 'cta_button_url', 'value' => '/register', 'type' => 'text'],
            ],
            'institucional_valores' => [
                ['key' => 'title', 'value' => 'Nossos Valores - UNN', 'type' => 'text'],
                ['key' => 'meta_title', 'value' => 'Nossos Valores - UNN', 'type' => 'text'],
                ['key' => 'meta_description', 'value' => 'Os princípios que guiam tudo o que fazemos na UNN.', 'type' => 'text'],
                ['key' => 'meta_robots', 'value' => 'index,follow', 'type' => 'text'],
                ['key' => 'og_type', 'value' => 'website', 'type' => 'text'],
                ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text'],

                ['key' => 'hero_title', 'value' => 'Nossos', 'type' => 'text'],
                ['key' => 'hero_title_highlight', 'value' => 'Valores', 'type' => 'text'],
                ['key' => 'hero_subtitle', 'value' => 'Os princípios que guiam tudo o que fazemos na UNN.', 'type' => 'text'],
                [
                    'key' => 'values_items',
                    'value' => json_encode([
                        [
                            'icon' => 'fas fa-heart',
                            'title' => 'Confiança',
                            'text' => 'A base de qualquer relacionamento duradouro. Cultivamos um ambiente onde a palavra tem valor e os compromissos são honrados. Confiança não se exige, se constrói.',
                            'quote' => '"Confiança é a cola invisível que mantém as parcerias unidas."',
                        ],
                        [
                            'icon' => 'fas fa-hands-helping',
                            'title' => 'Generosidade',
                            'text' => 'O verdadeiro networking começa quando você se pergunta: "Como posso ajudar?". Acreditamos que dar sem esperar nada em troca cria as conexões mais poderosas.',
                            'quote' => '"Quem planta conexões, colhe oportunidades."',
                        ],
                        [
                            'icon' => 'fas fa-lightbulb',
                            'title' => 'Inovação',
                            'text' => 'Nunca paramos de evoluir. Buscamos constantemente novas formas de conectar pessoas e gerar valor. A zona de conforto não é lugar para empreendedores.',
                            'quote' => '"Inovar é ver o que todos veem e pensar o que ninguém pensou."',
                        ],
                        [
                            'icon' => 'fas fa-trophy',
                            'title' => 'Excelência',
                            'text' => 'Buscamos sempre entregar mais do que prometemos. Excelência está no cuidado com os detalhes, no respeito com o tempo do outro e na dedicação aos nossos membros.',
                            'quote' => '"Excelência não é um ato, é um hábito."',
                        ],
                        [
                            'icon' => 'fas fa-user-shield',
                            'title' => 'Integridade',
                            'text' => 'Fazemos o que é certo, mesmo quando ninguém está olhando. A ética nos negócios não é opcional, é fundamental. Nossos membros são selecionados por seu caráter.',
                            'quote' => '"O caráter se revela nas pequenas decisões do dia a dia."',
                        ],
                        [
                            'icon' => 'fas fa-users',
                            'title' => 'Comunidade',
                            'text' => 'Somos mais fortes juntos. A UNN não é apenas uma plataforma, é uma família de empreendedores que se apoiam mutuamente nos desafios e celebram as vitórias um do outro.',
                            'quote' => '"Sozinhos vamos mais rápido. Juntos vamos mais longe."',
                        ],
                    ], JSON_UNESCAPED_UNICODE),
                    'type' => 'json',
                ],

                ['key' => 'quote_text', 'value' => '“Valores não são apenas palavras bonitas na parede. São os critérios pelos quais tomamos cada decisão, grandes ou pequenas, todos os dias.”', 'type' => 'text'],
                ['key' => 'quote_author', 'value' => '— Equipe Fundadora UNN', 'type' => 'text'],

                ['key' => 'cta_title', 'value' => 'Compartilha desses valores?', 'type' => 'text'],
                ['key' => 'cta_subtitle', 'value' => 'Você está no lugar certo. Faça parte da nossa comunidade.', 'type' => 'text'],
                ['key' => 'cta_button_text', 'value' => 'Fazer parte', 'type' => 'text'],
                ['key' => 'cta_button_url', 'value' => '/register', 'type' => 'text'],
            ],
            'institucional_contato' => [
                ['key' => 'title', 'value' => 'Contato - UNN', 'type' => 'text'],
                ['key' => 'meta_title', 'value' => 'Contato - UNN', 'type' => 'text'],
                ['key' => 'meta_description', 'value' => 'Entre em contato com a UNN por qualquer um dos nossos canais.', 'type' => 'text'],
                ['key' => 'meta_robots', 'value' => 'index,follow', 'type' => 'text'],
                ['key' => 'og_type', 'value' => 'website', 'type' => 'text'],
                ['key' => 'twitter_card', 'value' => 'summary_large_image', 'type' => 'text'],

                ['key' => 'hero_title', 'value' => 'Fale', 'type' => 'text'],
                ['key' => 'hero_title_highlight', 'value' => 'Conosco', 'type' => 'text'],
                ['key' => 'hero_subtitle', 'value' => 'Estamos aqui para ajudar. Entre em contato por qualquer um dos canais abaixo.', 'type' => 'text'],

                ['key' => 'map_title', 'value' => 'Nossa Localização', 'type' => 'text'],
            ],
        ];

        foreach ($pages as $slug => $items) {
            foreach ($items as $item) {
                $this->upsertIfEmptyOrMissing(
                    $slug,
                    (string) $item['key'],
                    (string) ($item['value'] ?? ''),
                    $hasType ? (string) ($item['type'] ?? 'text') : null,
                    $now
                );
            }
        }
    }

    private function upsertIfEmptyOrMissing(string $slug, string $key, string $value, ?string $type, $now): void
    {
        $existing = DB::table('site_contents')
            ->where('slug', $slug)
            ->where('key', $key)
            ->first();

        if (!$existing) {
            $insert = [
                'slug' => $slug,
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($type !== null) {
                $insert['type'] = $type;
            }

            DB::table('site_contents')->insert($insert);
            return;
        }

        if (trim((string) ($existing->value ?? '')) !== '') {
            return;
        }

        $update = [
            'value' => $value,
            'updated_at' => $now,
        ];

        if ($type !== null && trim((string) ($existing->type ?? '')) === '') {
            $update['type'] = $type;
        }

        DB::table('site_contents')->where('id', (int) $existing->id)->update($update);
    }

    public function down(): void
    {
        // no-op (safety)
    }
};

