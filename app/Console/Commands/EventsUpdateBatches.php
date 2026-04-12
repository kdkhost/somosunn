<?php
// UTF-8 sem BOM
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class EventsUpdateBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-batches';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e sinaliza as viradas de lotes dos eventos ativos baseados na data e horario atual.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Iniciando varredura de virada de lotes dos Eventos...");
        Log::info("Crontask events:update-batches iniciado.");

        // Pega todos os eventos publicados que tenham pelo menos um lote configurado
        $events = Event::where('published', true)
            ->where(function($query) {
                $query->whereNotNull('batch_1_price')
                      ->orWhereNotNull('batch_2_price')
                      ->orWhereNotNull('batch_3_price');
            })
            ->get();

        $changedCount = 0;

        foreach ($events as $event) {
            // Se o evento estiver encerrado no tempo, pulamos
            if ($event->isClosedForPublic()) {
                continue;
            }

            // Descobre o lote ativo matematicamente em tempo real
            $activeBatchLabel = $event->currentBatchLabelFor(null);
            
            // Verifica no cache qual era o ultimo lote conhecido deste evento
            $cacheKey = "event_{$event->id}_last_active_batch";
            $lastKnownBatch = cache()->get($cacheKey);

            if ($lastKnownBatch !== $activeBatchLabel) {
                // Aconteceu a virada de lote!
                cache()->forever($cacheKey, $activeBatchLabel);
                $changedCount++;

                $msg = "O Evento ID {$event->id} ({$event->title}) teve seu lote atualizado para: {$activeBatchLabel}.";
                $this->info($msg);
                Log::info($msg);
                
                // Futuramente podemos disparar um dispatch / broadcast de notificaçao aqui se desejado.
            }
        }

        $this->info("Processamento finalizado. {$changedCount} evento(s) transicionaram de lote.");
        return 0;
    }
}
