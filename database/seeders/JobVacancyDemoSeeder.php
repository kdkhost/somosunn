<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class JobVacancyDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Garantir que temos um usuário admin ou empresa para associar as vagas
        $admin = User::where('email', 'admin@somosunn.com.br')->first();

        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin Somos UNN',
                'email' => 'admin@somosunn.com.br',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]);
        }

        $vagas = [
            [
                'title' => 'Desenvolvedor Full Stack PHP (Laravel)',
                'company_name' => 'UNN Tecnologia',
                'location' => 'Ribeirão Preto, SP (Híbrido)',
                'type' => 'CLT',
                'short_description' => 'Buscamos um desenvolvedor apaixonado por código limpo e soluções escaláveis para integrar nosso time core.',
                'description' => '<h3>Sobre a vaga</h3><p>Como Desenvolvedor Full Stack na UNN, você será responsável por manter e evoluir nossa plataforma principal, trabalhando com as tecnologias mais modernas do ecossistema PHP.</p><h3>Responsabilidades</h3><ul><li>Desenvolver novas funcionalidades no frontend e backend</li><li>Otimizar performance de consultas ao banco de dados</li><li>Participar de revisões de código</li><li>Colaborar com o time de Produto</li></ul>',
                'requirements' => '<ul><li>Sólida experiência com PHP 8 e Laravel</li><li>Domínio de JavaScript (Vue.js ou Alpine.js)</li><li>Experiência com Tailwind CSS</li><li>Conhecimento em bancos de dados relacionais (PostgreSQL/MySQL)</li><li>Experiência com Docker e Git</li></ul>',
                'benefits' => '<ul><li>Salário competitivo</li><li>Vale Refeição / Alimentação</li><li>Plano de Saúde e Odontológico</li><li>Horário Flexível</li><li>Participação em lucros e resultados</li></ul>',
                'salary_range' => 'R$ 8.500,00 - R$ 12.000,00',
                'visibility' => 'public',
                'is_active' => true,
                'is_demo' => false,
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'Designer UI/UX Sênior',
                'company_name' => 'Somus Digital',
                'location' => 'Remoto',
                'type' => 'PJ',
                'short_description' => 'Procuramos um designer com olhar crítico para criar interfaces incríveis e jornadas de usuário simplificadas.',
                'description' => '<h3>O desafio</h3><p>Sua missão será transformar problemas complexos em soluções de design elegantes e intuitivas, garantindo a melhor experiência para os usuários finais.</p><h3>O que você fará</h3><ul><li>Criar wireframes e protótipos de alta fidelidade</li><li>Realizar testes de usabilidade</li><li>Manter e evoluir nosso Design System</li><li>Trabalhar próximo aos desenvolvedores para garantir a fidelidade visual</li></ul>',
                'requirements' => '<ul><li>Mínimo de 5 anos de experiência em Web/Mobile Design</li><li>Domínio do Figma</li><li>Portfólio com casos de estudo reais</li><li>Facilidade de comunicação e trabalho em equipe</li><li>Desejável conhecimento de HTML/CSS (capacidade técnica)</li></ul>',
                'benefits' => '<ul><li>Trabalho 100% Remoto</li><li>Auxílio Home Office</li><li>Ambiente de aprendizado contínuo</li><li>Bonificação anual por metas</li></ul>',
                'salary_range' => 'R$ 10.000,00 - R$ 14.000,00',
                'visibility' => 'public',
                'is_active' => true,
                'is_demo' => true,
                'expires_at' => now()->addDays(45),
            ],
            [
                'title' => 'Gestor de Tráfego Pago',
                'company_name' => 'Agência Performance Max',
                'location' => 'São Paulo, SP (Presencial)',
                'type' => 'CLT',
                'short_description' => 'Especialista em anúncios (Facebook, Google e TikTok Ads) para escala de infoprodutos e e-commerce.',
                'description' => '<h3>Missão</h3><p>Gerenciar grandes orçamentos diários garantindo o menor CPA possível e o maior ROAS do mercado.</p><h3>Atividades</h3><ul><li>Criação e gestão de campanhas</li><li>Análise profunda de métricas</li><li>Copy para anúncios</li><li>Planejamento estratégico de mídia</li></ul>',
                'requirements' => '<ul><li>Experiência comprovada com Google Ads e FacebookAds</li><li>Domínio de Google Analytics e Tag Manager</li><li>Foco total em resultados (Data-driven)</li><li>Excel Avançado / Dashboards</li></ul>',
                'benefits' => '<ul><li>Vale Refeição de R$ 35/dia</li><li>Vale Transporte</li><li>Seguro de Vida</li><li>Bônus por performance de escala</li></ul>',
                'salary_range' => 'R$ 5.000,00 + Comissões',
                'visibility' => 'public',
                'is_active' => true,
                'is_demo' => true,
                'expires_at' => now()->addDays(20),
            ],
            [
                'title' => 'Social Media & Copywriter',
                'company_name' => 'Inova Marketing',
                'location' => 'Curitiba, PR (Híbrido)',
                'type' => 'Freelance',
                'short_description' => 'Procuro freelancer para gestão de 3 perfis de clientes premium com foco em engajamento e branding.',
                'description' => '<h3>Sobre o job</h3><p>Planejamento mensal de conteúdo estratégico para redes sociais (Instagram, LinkedIn e Twitter).</p><h3>Entregas esperadas</h3><ul><li>Calendário editorial mensal</li><li>Criação de legendas persuasivas (Copy)</li><li>Roteiros para Reels/TikTok</li><li>Relatório mensal de insights</li></ul>',
                'requirements' => '<ul><li>Portfólio de cases em redes sociais</li><li>Excelente escrita em português</li><li>Criatividade aflorada</li><li>Experiência com Canva ou Adobe Express</li></ul>',
                'benefits' => '<ul><li>Pagamento pontual</li><li>Acesso a cursos de especialização</li><li>Possibilidade de contrato fixo a longo prazo</li></ul>',
                'salary_range' => 'R$ 2.500,00 / mês',
                'visibility' => 'public',
                'is_active' => true,
                'is_demo' => true,
                'expires_at' => now()->addDays(15),
            ],
        ];

        foreach ($vagas as $vaga) {
            $vaga['user_id'] = $admin->id;
            JobVacancy::create($vaga);
        }
    }
}
