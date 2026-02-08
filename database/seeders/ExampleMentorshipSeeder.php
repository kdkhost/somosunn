<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mentorship;
use App\Models\User;

class ExampleMentorshipSeeder extends Seeder
{
    public function run()
    {
        $mentor = User::whereIn('role', ['admin', 'superadmin'])->first();
        $mentorId = $mentor ? $mentor->id : 1;

        Mentorship::updateOrCreate(
            ['title' => 'Conexão Elite: Mentoria de Negócios 2026'],
            [
                'mentor_id' => $mentorId,
                'description' => 'Uma mentoria exclusiva focada em estratégias de escala, networking de alto nível e automação de processos para empresários que desejam dobrar seu faturamento.',
                'price' => 1497.00,
                'slots' => 12,
                'schedule' => [
                    'Terça-feira' => '20:00 - 22:00',
                    'Quinta-feira' => '20:00 - 22:00'
                ],
                'type' => Mentorship::TYPE_ONLINE,
                'video_platform' => Mentorship::PLATFORM_MEET,
                'video_link' => 'https://meet.google.com/abc-defn-ghi',
                'demo_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
            ]
        );

        echo "Exemplo de mentoria publicado com sucesso!\n";
    }
}
