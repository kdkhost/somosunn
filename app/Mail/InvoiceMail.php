<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
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

        $layout = app(SystemMailLayoutData::class)->make();
        if (!empty($company['name'])) {
            $layout['siteName'] = (string) $company['name'];
        }
        if (!empty($company['logo_url'])) {
            $layout['logoUrl'] = (string) $company['logo_url'];
        }
        if (!empty($company['primary_color'])) {
            $layout['primaryColor'] = (string) $company['primary_color'];
        }

        return $this
            ->subject('Fatura ' . $number . ' - ' . ($company['name'] ?? config('app.name')))
            ->view('emails.invoice', array_merge($layout, [
                'invoice' => $this->invoice,
                'company' => $company,
            ]))
            ->attachData($this->pdfBytes, $filename, ['mime' => 'application/pdf']);
    }
}
