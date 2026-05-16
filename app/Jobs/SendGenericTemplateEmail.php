<?php

namespace App\Jobs;

use App\Services\Mail\SystemMailLayoutData;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendGenericTemplateEmail implements ShouldQueue
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

    public string $toEmail;
    public string $subject;
    public string $htmlContent;

    public function __construct(string $toEmail, string $subject, string $htmlContent)
    {
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        // Atribuicao via metodo do trait Queueable evita FatalError
        // de redeclaracao de propriedade em PHP 8.4+.
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());
    }

    public function handle(): void
    {
        $layout = app(SystemMailLayoutData::class)->make();

        Mail::send('emails.system', array_merge($layout, ['content' => $this->htmlContent]), function ($message) {
            $message->to($this->toEmail)->subject($this->subject);
        });
    }
}
