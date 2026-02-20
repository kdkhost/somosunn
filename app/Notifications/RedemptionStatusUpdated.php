<?php

namespace App\Notifications;

use App\Models\Redemption;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RedemptionStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $redemption;

    public function __construct(Redemption $redemption)
    {
        $this->redemption = $redemption;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabel = $this->redemption->status === 'completed' ? 'Concluído' : 'Cancelado';
        $message = $this->redemption->status === 'completed'
            ? 'Seu pedido de resgate do item **' . $this->redemption->item->name . '** foi processado e concluído!'
            : 'Seu pedido de resgate do item **' . $this->redemption->item->name . '** foi cancelado. Seus pontos foram devolvidos ao seu saldo.';

        return (new MailMessage)
            ->subject('Atualização no seu Resgate: ' . $statusLabel)
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($message)
            ->action('Ver Meus Resgates', route('panel.redemptions.history'))
            ->line('Obrigado por fazer parte da nossa comunidade!')
            ->salutation('Atenciosamente, Equipe SOMOS UNN');
    }

    public function toArray($notifiable): array
    {
        $statusLabel = $this->redemption->status === 'completed' ? 'Concluído' : 'Cancelado';

        return [
            'message' => 'Seu resgate de "' . ($this->redemption->item->name ?? 'item') . '" foi ' . strtolower($statusLabel) . '.',
            'type' => 'redemption_update',
            'action_url' => route('panel.redemptions.history'),
            'action_label' => 'Ver Histórico',
            'status' => $this->redemption->status,
        ];
    }
}
