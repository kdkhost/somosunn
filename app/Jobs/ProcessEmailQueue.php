<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessEmailQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Queue dedicada para este job (alinhada com QueueManagerService).
    /**
     * Tentativas em caso de falha.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Timeout em segundos.
     *
     * @var int
     */
    public $timeout = 60;

    /**
     * Backoff em segundos entre tentativas.
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Atribuicao via metodo do trait Queueable evita FatalError
        // de redeclaracao de propriedade em PHP 8.4+.
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
