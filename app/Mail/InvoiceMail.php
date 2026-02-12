<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
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

        $siteUrl = url('/');

        $data = [
            'year' => date('Y'),
            'user' => [
                'name' => (string) ($this->invoice->user?->name ?? 'Cliente'),
                'email' => (string) ($this->invoice->user?->email ?? ''),
            ],
            'site' => [
                'name' => (string) ($layout['siteName'] ?? config('app.name', 'UNN')),
                'url' => $siteUrl,
                'logo' => (string) ($layout['logoUrl'] ?? asset('img/logo.svg')),
                'primary_color' => (string) ($layout['primaryColor'] ?? '#1F5EDB'),
                'secondary_color' => (string) ($layout['secondaryColor'] ?? '#177FD6'),
            ],
            'invoice' => [
                'id' => (string) $this->invoice->id,
                'number' => (string) $number,
                'issued_at' => ($this->invoice->issued_at ? $this->invoice->issued_at->format('d/m/Y') : now()->format('d/m/Y')),
                'due_at' => ($this->invoice->due_at ? $this->invoice->due_at->format('d/m/Y') : ''),
                'total' => 'R$ ' . number_format((float) ($this->invoice->total_amount ?? 0), 2, ',', '.'),
            ],
        ];

        $rendered = app(SystemMailTemplateService::class)->renderBySlug('invoice_email', $data);
        if ($rendered) {
            return $this
                ->subject($rendered['subject'])
                ->view('emails.system', array_merge($layout, [
                    'content' => $rendered['content'],
                ]))
                ->attachData($this->pdfBytes, $filename, ['mime' => 'application/pdf']);
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
