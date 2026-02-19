<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('panel.invoices.show', $this->invoice->id); // Assuming this route exists for user panel

        return (new MailMessage)
            ->subject('Lembrete: Fatura #' . $this->invoice->number . ' vencida')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Consta em nosso sistema que a fatura #' . $this->invoice->number . ' venceu ontem (' . $this->invoice->due_at->format('d/m/Y') . ').')
            ->line('Valor: R$ ' . number_format($this->invoice->total_amount, 2, ',', '.'))
            ->action('Visualizar Fatura', $url)
            ->line('Caso já tenha efetuado o pagamento, por favor, desconsidere este e-mail.')
            ->line('Obrigado!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->total_amount,
            'due_at' => $this->invoice->due_at,
        ];
    }
}
