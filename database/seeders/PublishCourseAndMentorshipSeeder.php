<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PublishCourseAndMentorshipSeeder extends Seeder
{
    public function run(): void
    {
        $mentor = User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->orderBy('id')
            ->first();

        if (!$mentor) {
            $mentor = User::query()->orderBy('id')->first();
        }

        $mentorId = $mentor?->id ?? 1;
        $mentorName = $mentor?->name ?? 'Equipe UNN';

        $certificateBg = 'uploads/certificates/cert_bg_1770265769_69841ca9cf132.png';

        $courseTitle = 'Networking Estratégico 360: Conexões que Geram Negócios';
        $courseShort = 'Acelere suas oportunidades com um método prático de networking em 4 aulas diretas ao ponto.';
        $courseFull = <<<HTML
<p>Um curso direto ao ponto para transformar conexões em resultados reais.</p>
<ul>
    <li>Diagnóstico da sua rede e definição de metas claras.</li>
    <li>Posicionamento e proposta de valor percebido.</li>
    <li>Roteiros de conversa e follow-up estratégico.</li>
    <li>Plano de 30 dias para manter o pipeline ativo.</li>
</ul>
<p>Ao concluir, você recebe certificado e um roteiro de ação para aplicar imediatamente.</p>
HTML;

        $courseData = $this->filterExistingColumns('courses', [
            'user_id' => $mentorId,
            'title' => $courseTitle,
            'price' => 997.00,
            'duration' => 60,
            'short_description' => $courseShort,
            'full_description' => $courseFull,
            'author_name' => $mentorName,
            'status' => 'published',
            'published' => true,
            'thumbnail' => 'uploads/course-thumbs/1770237560_christmas-wallpaper-from-designyourway-1200x700.jpg',
        ]);

        $course = Course::updateOrCreate(['title' => $courseTitle], $courseData);

        $courseCertSettings = $this->buildCertificateSettings(
            'CERTIFICADO DE CONCLUSÃO',
            "Este certificado reconhece a conclusão do curso {$courseTitle}.",
            $mentorName
        );

        $course->fill($this->filterExistingColumns('courses', [
            'is_certificate_enabled' => true,
            'certificate_bg' => $certificateBg,
            'certificate_settings' => $courseCertSettings,
        ]))->save();

        $course->lessons()->delete();

        $lessons = [
            [
                'title' => 'Aula 1 - Diagnóstico de rede e metas',
                'order' => 1,
                'video_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
                'content' => 'Mapeie sua rede atual, identifique lacunas e defina metas claras para os próximos 30 dias.',
                'duration' => 600,
                'is_free_preview' => true,
                'free_preview_mode' => 'time',
                'free_preview_seconds' => 30,
            ],
            [
                'title' => 'Aula 2 - Posicionamento e proposta de valor',
                'order' => 2,
                'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
                'content' => 'Construa uma proposta de valor clara e um posicionamento que gere confiança e interesse.',
                'duration' => 900,
                'is_free_preview' => false,
                'free_preview_mode' => 'full',
                'free_preview_seconds' => null,
            ],
            [
                'title' => 'Aula 3 - Conversas estratégicas e follow-up',
                'order' => 3,
                'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
                'content' => 'Aprenda a conduzir conversas produtivas e a manter follow-ups que geram retorno.',
                'duration' => 1200,
                'is_free_preview' => false,
                'free_preview_mode' => 'full',
                'free_preview_seconds' => null,
            ],
            [
                'title' => 'Aula 4 - Plano de ação de 30 dias',
                'order' => 4,
                'video_url' => 'https://www.youtube.com/watch?v=2OEL4P1Rz04',
                'content' => 'Monte seu plano de ação com metas semanais e indicadores de avanço.',
                'duration' => 900,
                'is_free_preview' => false,
                'free_preview_mode' => 'full',
                'free_preview_seconds' => null,
            ],
        ];

        foreach ($lessons as $lesson) {
            $course->lessons()->create($this->filterExistingColumns('lessons', $lesson));
        }

        $mentorshipTitle = 'Mentoria Individual - Networking de Alto Impacto';
        $mentorshipDesc = <<<HTML
<p>Mentoria 1:1 para destravar oportunidades e aumentar sua taxa de fechamento.</p>
<ul>
    <li>Diagnóstico do seu posicionamento atual.</li>
    <li>Plano de relacionamento com decisores-chave.</li>
    <li>Acompanhamento semanal com metas e feedbacks.</li>
</ul>
<p>Ideal para profissionais que buscam orientação prática e resultados rápidos.</p>
HTML;

        $mentorshipData = $this->filterExistingColumns('mentorships', [
            'mentor_id' => $mentorId,
            'title' => $mentorshipTitle,
            'description' => $mentorshipDesc,
            'price' => 1297.00,
            'slots' => 8,
            'schedule' => [
                'Segunda-feira' => '19:00 - 20:30',
                'Quinta-feira' => '19:00 - 20:30',
            ],
            'type' => Mentorship::TYPE_ONLINE,
            'video_platform' => Mentorship::PLATFORM_MEET,
            'video_link' => 'https://meet.google.com/abc-defg-hij',
            'demo_link' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
            'image' => 'uploads/mentorship-images/mentoria-individual-unn-2026.jpg',
        ]);

        $mentorship = Mentorship::updateOrCreate(['title' => $mentorshipTitle], $mentorshipData);

        $mentorshipCertSettings = $this->buildCertificateSettings(
            'CERTIFICADO DE CONCLUSÃO',
            "Este certificado reconhece a conclusão da mentoria {$mentorshipTitle}.",
            $mentorName
        );

        $mentorship->fill($this->filterExistingColumns('mentorships', [
            'is_certificate_enabled' => true,
            'certificate_bg' => $certificateBg,
            'certificate_settings' => $mentorshipCertSettings,
        ]))->save();

        $this->command?->info('Curso e mentoria publicados/atualizados com certificados.');
    }

    private function filterExistingColumns(string $table, array $data): array
    {
        $filtered = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn($table, $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function buildCertificateSettings(string $titleText, string $presentationText, string $authorName): array
    {
        return [
            'schemaVersion' => 2,
            'meta' => [
                'backgroundFit' => 'cover',
                'titleText' => $titleText,
                'presentationText' => $presentationText,
            ],
            'elements' => [
                'student_name' => [
                    'x' => 50,
                    'y' => 40,
                    'text' => '[Nome do Aluno]',
                    'fontSize' => 30,
                    'color' => '#000000',
                    'fontWeight' => 'bold',
                    'fontFamily' => 'Arial, sans-serif',
                ],
                'course_name' => [
                    'x' => 50,
                    'y' => 55,
                    'text' => '[Nome do Curso]',
                    'fontSize' => 24,
                    'color' => '#333333',
                    'fontWeight' => 'bold',
                    'fontFamily' => 'Arial, sans-serif',
                ],
                'completion_date' => [
                    'x' => 50,
                    'y' => 65,
                    'text' => 'Concluído em: 01/01/2026',
                    'fontSize' => 16,
                    'color' => '#555555',
                    'fontWeight' => 'normal',
                    'fontFamily' => 'Arial, sans-serif',
                ],
                'certificate_code' => [
                    'x' => 50,
                    'y' => 85,
                    'text' => 'Validação: ABC-123',
                    'fontSize' => 12,
                    'color' => '#999999',
                    'fontWeight' => 'normal',
                    'fontFamily' => 'Arial, sans-serif',
                ],
                'author_name' => [
                    'x' => 30,
                    'y' => 90,
                    'text' => $authorName,
                    'fontSize' => 18,
                    'color' => '#333333',
                    'fontWeight' => 'bold',
                    'fontFamily' => 'Arial, sans-serif',
                    'zIndex' => 10,
                ],
                'workload_hours' => [
                    'x' => 70,
                    'y' => 90,
                    'text' => 'Carga Horária: 1h',
                    'fontSize' => 14,
                    'color' => '#666666',
                    'fontWeight' => 'normal',
                    'fontFamily' => 'Arial, sans-serif',
                    'zIndex' => 10,
                ],
                'title' => [
                    'x' => 10,
                    'y' => 18,
                    'text' => $titleText,
                    'fontSize' => 34,
                    'color' => '#000000',
                    'fontWeight' => 'bold',
                    'fontFamily' => 'Arial, sans-serif',
                    'zIndex' => 15,
                    'visible' => false,
                    'multiline' => true,
                    'maxWidth' => 700,
                    'textAlign' => 'center',
                ],
                'presentation_text' => [
                    'x' => 10,
                    'y' => 28,
                    'text' => $presentationText,
                    'fontSize' => 16,
                    'color' => '#333333',
                    'fontWeight' => 'normal',
                    'fontFamily' => 'Arial, sans-serif',
                    'zIndex' => 15,
                    'visible' => false,
                    'multiline' => true,
                    'maxWidth' => 700,
                    'textAlign' => 'center',
                ],
                'instructor_signature' => [
                    'x' => 70,
                    'y' => 80,
                    'text' => 'Assinatura do Instrutor',
                    'fontSize' => 12,
                    'color' => '#6c757d',
                    'fontWeight' => 'normal',
                    'fontFamily' => 'Arial, sans-serif',
                    'width' => 200,
                    'height' => 60,
                    'zIndex' => 10,
                    'visible' => false,
                ],
                'platform_logo' => [
                    'x' => 50,
                    'y' => 10,
                    'text' => 'LOGO',
                    'fontSize' => 36,
                    'color' => '#0066cc',
                    'fontWeight' => 'bold',
                    'fontFamily' => 'Georgia, serif',
                    'width' => 120,
                    'height' => 60,
                    'mandatory' => true,
                    'zIndex' => 20,
                ],
            ],
        ];
    }
}
