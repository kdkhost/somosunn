<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    private string $pdfBytes;

    public function __construct(Invoice $invoice, string $pdfBytes)
    {
        $this->invoice = $invoice;
        $this->pdfBytes = $pdfBytes;
    }

    public function build()
    {
        $company = app(InvoiceService::class)->companyInfo();
        $number = $this->invoice->number ?: ('#' . $this->invoice->id);

        $filename = 'Fatura-' . ($this->invoice->number ?: $this->invoice->id) . '.pdf';

        return $this
            ->subject('Fatura ' . $number . ' - ' . ($company['name'] ?? config('app.name')))
            ->view('emails.invoice', [
                'invoice' => $this->invoice,
                'company' => $company,
            ])
            ->attachData($this->pdfBytes, $filename, ['mime' => 'application/pdf']);
    }
}

