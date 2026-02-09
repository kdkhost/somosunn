<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PointsRule;

class PointsRulesSeeder extends Seeder
{
    public function run()
    {
        $rules = [
            // Engajamento
            [
                'key' => 'signup',
                'label' => 'Cadastro na plataforma',
                'description' => 'Pontos concedidos ao criar conta',
                'category' => 'engajamento',
                'points' => 50,
                'icon' => 'fas fa-user-plus',
                'active' => true,
                'repeatable' => false,
            ],
            [
                'key' => 'daily_login',
                'label' => 'Login diário',
                'description' => 'Pontos por acessar a plataforma diariamente',
                'category' => 'engajamento',
                'points' => 5,
                'icon' => 'fas fa-calendar-check',
                'active' => true,
                'repeatable' => true,
                'max_daily' => 1,
            ],
            [
                'key' => 'complete_profile',
                'label' => 'Completar perfil',
                'description' => 'Preencher foto, bio e informações',
                'category' => 'engajamento',
                'points' => 30,
                'icon' => 'fas fa-id-card',
                'active' => true,
                'repeatable' => false,
            ],
            [
                'key' => 'streak_7days',
                'label' => 'Sequência de 7 dias',
                'description' => 'Bônus por 7 dias consecutivos de acesso',
                'category' => 'engajamento',
                'points' => 50,
                'icon' => 'fas fa-fire',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'streak_30days',
                'label' => 'Sequência de 30 dias',
                'description' => 'Bônus por 30 dias consecutivos de acesso',
                'category' => 'engajamento',
                'points' => 200,
                'icon' => 'fas fa-fire-alt',
                'active' => true,
                'repeatable' => true,
            ],

            // Aprendizado
            [
                'key' => 'complete_lesson',
                'label' => 'Concluir aula',
                'description' => 'Pontos por completar uma aula',
                'category' => 'aprendizado',
                'points' => 5,
                'icon' => 'fas fa-check-circle',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'complete_course',
                'label' => 'Concluir curso',
                'description' => 'Pontos por finalizar um curso completo',
                'category' => 'aprendizado',
                'points' => 100,
                'icon' => 'fas fa-graduation-cap',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'earn_certificate',
                'label' => 'Obter certificado',
                'description' => 'Pontos ao gerar certificado de conclusão',
                'category' => 'aprendizado',
                'points' => 50,
                'icon' => 'fas fa-certificate',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'attend_event',
                'label' => 'Participar de evento',
                'description' => 'Pontos por comparecer a um evento',
                'category' => 'aprendizado',
                'points' => 30,
                'icon' => 'fas fa-calendar-alt',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'attend_mentorship',
                'label' => 'Participar de mentoria',
                'description' => 'Pontos por sessão de mentoria concluída',
                'category' => 'aprendizado',
                'points' => 40,
                'icon' => 'fas fa-chalkboard-teacher',
                'active' => true,
                'repeatable' => true,
            ],

            // Comunidade
            [
                'key' => 'publish_post',
                'label' => 'Publicar no feed',
                'description' => 'Criar uma publicação no feed da comunidade',
                'category' => 'comunidade',
                'points' => 10,
                'icon' => 'fas fa-pen',
                'active' => true,
                'repeatable' => true,
                'max_daily' => 5,
            ],
            [
                'key' => 'comment',
                'label' => 'Comentar',
                'description' => 'Pontos por comentar em posts',
                'category' => 'comunidade',
                'points' => 3,
                'icon' => 'fas fa-comment',
                'active' => true,
                'repeatable' => true,
                'max_daily' => 20,
            ],
            [
                'key' => 'receive_like',
                'label' => 'Receber curtida',
                'description' => 'Quando seu conteúdo é curtido',
                'category' => 'comunidade',
                'points' => 1,
                'icon' => 'fas fa-heart',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'help_member',
                'label' => 'Ajudar membro',
                'description' => 'Resposta marcada como útil por outro membro',
                'category' => 'comunidade',
                'points' => 15,
                'icon' => 'fas fa-hands-helping',
                'active' => true,
                'repeatable' => true,
            ],

            // Conquistas
            [
                'key' => 'first_course',
                'label' => 'Primeiro curso',
                'description' => 'Bônus por concluir o primeiro curso',
                'category' => 'conquistas',
                'points' => 100,
                'icon' => 'fas fa-trophy',
                'active' => true,
                'repeatable' => false,
            ],
            [
                'key' => 'top_10_ranking',
                'label' => 'Top 10 no ranking',
                'description' => 'Entrar no top 10 do ranking mensal',
                'category' => 'conquistas',
                'points' => 200,
                'icon' => 'fas fa-medal',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'mentor',
                'label' => 'Tornar-se mentor',
                'description' => 'Bônus por oferecer primeira mentoria',
                'category' => 'conquistas',
                'points' => 100,
                'icon' => 'fas fa-crown',
                'active' => true,
                'repeatable' => false,
            ],

            // Bônus
            [
                'key' => 'referral',
                'label' => 'Indicar amigo',
                'description' => 'Quando um amigo indicado se cadastra',
                'category' => 'bonus',
                'points' => 100,
                'icon' => 'fas fa-user-friends',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'review',
                'label' => 'Avaliar conteúdo',
                'description' => 'Deixar avaliação em curso ou evento',
                'category' => 'bonus',
                'points' => 10,
                'icon' => 'fas fa-star',
                'active' => true,
                'repeatable' => true,
            ],
            [
                'key' => 'share_social',
                'label' => 'Compartilhar nas redes',
                'description' => 'Compartilhar conteúdo nas redes sociais',
                'category' => 'bonus',
                'points' => 5,
                'icon' => 'fas fa-share-alt',
                'active' => true,
                'repeatable' => true,
                'max_daily' => 3,
            ],
            [
                'key' => 'birthday_bonus',
                'label' => 'Bônus de aniversário',
                'description' => 'Pontos especiais no dia do aniversário',
                'category' => 'bonus',
                'points' => 100,
                'icon' => 'fas fa-birthday-cake',
                'active' => true,
                'repeatable' => true,
            ],
        ];

        $sortOrder = 0;
        foreach ($rules as $rule) {
            $rule['sort_order'] = $sortOrder++;
            PointsRule::updateOrCreate(
                ['key' => $rule['key']],
                $rule
            );
        }
    }
}
