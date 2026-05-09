<?php

namespace App\Mail;

use App\Mail\Concerns\UsesMailTemplate;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels, UsesMailTemplate;

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

        $mail = $this->buildFromTemplate('invoice_sent', [
            'user' => [
                'name' => $this->invoice->user->name ?? 'Cliente',
                'email' => $this->invoice->user->email ?? '',
            ],
            'invoice' => [
                'number' => $number,
                'amount' => 'R$ ' . number_format((float) $this->invoice->total_amount, 2, ',', '.'),
                'due_date' => optional($this->invoice->due_at)->format('d/m/Y') ?? '-',
                'status' => $this->invoice->status ?? 'pending',
            ],
            'company' => [
                'name' => $company['name'] ?? config('app.name'),
            ],
        ], [
            'name' => 'Fatura Enviada',
            'category' => 'financeiro',
            'subject' => 'Fatura {{invoice.number}} - {{company.name}}',
            'body' => '<h2>Olá, {{user.name}}!</h2>
<p>Sua fatura <strong>{{invoice.number}}</strong> no valor de <strong>{{invoice.amount}}</strong> foi gerada.</p>
<p>Vencimento: <strong>{{invoice.due_date}}</strong></p>
<p>O PDF da fatura está anexo a este e-mail.</p>
<p style="margin-top: 22px;">Atenciosamente,<br>{{company.name}}</p>',
        ]);

        return $mail->attachData($this->pdfBytes, $filename, ['mime' => 'application/pdf']);
    }
}
