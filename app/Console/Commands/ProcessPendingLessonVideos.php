<?php

namespace App\Console\Commands;

use App\Services\LessonVideoService;
use Illuminate\Console\Command;

class ProcessPendingLessonVideos extends Command
{
    protected $signature = 'lessons:process-pending-videos {--limit=2 : Quantidade maxima de aulas por execucao}';
    protected $description = 'Processa em lote as aulas com conversao de video pendente.';

    public function handle(LessonVideoService $service): int
    {
        $limite = (int) $this->option('limit');
        if ($limite <= 0) {
            $limite = 2;
        }

        $resumo = $service->processarConversoesPendentes($limite);

        $total = (int) ($resumo['total'] ?? 0);
        $processados = (int) ($resumo['processados'] ?? 0);
        $falhas = (int) ($resumo['falhas'] ?? 0);

        if ($total === 0) {
            $this->line('Nenhuma aula pendente para conversao.');
            return self::SUCCESS;
        }

        $this->line('Conversoes avaliadas: ' . $total . ' | prontas: ' . $processados . ' | falhas: ' . $falhas);

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}
