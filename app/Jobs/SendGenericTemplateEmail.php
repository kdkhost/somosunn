<?php

namespace App\Jobs;

use App\Services\Mail\SystemMailTemplateService;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        app(SystemMailTemplateService::class)->send('generic_system_email', $this->toEmail, [
            'message' => [
                'subject' => $this->subject,
                'content' => $this->htmlContent,
            ],
        ], [
            'name' => 'Email Generico do Sistema',
            'category' => 'sistema',
            'subject' => '{message.subject}',
            'body' => '{message.content}',
        ]);
    }
}
