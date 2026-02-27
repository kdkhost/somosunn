<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Services\LessonVideoService;
use Illuminate\Console\Command;

class ProcessLessonVideo extends Command
{
    protected $signature = 'lessons:process-video {lesson : ID da aula} {--force : Forca reprocessamento mesmo se ja estiver pronto}';
    protected $description = 'Processa a conversao protegida de video (HLS) para uma aula especifica.';

    public function handle(LessonVideoService $service): int
    {
        $lessonId = (int) $this->argument('lesson');
        $forcar = (bool) $this->option('force');

        $lesson = Lesson::query()->find($lessonId);
        if (!$lesson) {
            $this->error('Aula nao encontrada: #' . $lessonId);
            return self::FAILURE;
        }

        $resultado = $service->processarConversaoAula($lesson, $forcar);
        $status = (string) ($resultado['status'] ?? LessonVideoService::STATUS_FAILED);
        $mensagem = (string) ($resultado['message'] ?? '');

        if ($mensagem !== '') {
            $this->line($mensagem);
        }

        if ($status === LessonVideoService::STATUS_FAILED) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
