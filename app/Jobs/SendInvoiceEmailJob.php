<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\Middleware\RateLimited;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $invoiceId;
    public bool $force;

    public function __construct(int $invoiceId, bool $force = false)
    {
        $this->invoiceId = $invoiceId;
        $this->force = $force;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function middleware()
    {
        return [new RateLimited('invoices_email')];
    }

    public function handle(InvoiceService $service): void
    {
        $invoice = Invoice::with(['user', 'items', 'order'])->find($this->invoiceId);
        if (!$invoice) {
            return;
        }

        if (!$invoice->user || empty($invoice->user->email)) {
            return;
        }

        if (!$this->force && $invoice->email_sent_at) {
            return;
        }

        try {
            $pdfBytes = $service->generatePdfBytes($invoice);

            Mail::to($invoice->user->email)->send(new InvoiceMail($invoice, $pdfBytes));

            $invoice->email_sent_at = now();
            $invoice->save();
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar fatura #' . $invoice->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
}
