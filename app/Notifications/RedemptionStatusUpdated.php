<?php

namespace App\Notifications;

use App\Models\Redemption;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RedemptionStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected Redemption $redemption;

    public function __construct(Redemption $redemption)
    {
        $this->redemption = $redemption;
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $payload = $this->statusPayload();
        $itemName = (string) ($this->redemption->item->name ?? 'Item resgatado');

        $mail = (new MailMessage)
            ->subject('Atualização no seu Resgate: ' . $payload['label'])
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line(str_replace(':item', $itemName, $payload['message']));

        if ($this->redemption->provider_name) {
            $mail->line('Responsável: **' . $this->redemption->provider_name . '**');
        }

        if ($this->redemption->tracking_code) {
            $mail->line('Código de rastreio: **' . $this->redemption->tracking_code . '**');
        }

        if ($this->redemption->tracking_url) {
            $mail->line('Acompanhe a entrega: ' . $this->redemption->tracking_url);
        }

        return $mail
            ->action('Ver Meus Resgates', route('panel.redemptions.history'))
            ->line('Obrigado por fazer parte da nossa comunidade.')
            ->salutation('Atenciosamente, Equipe SOMOS UNN');
    }

    public function toArray($notifiable): array
    {
        $payload = $this->statusPayload();

        return [
            'message' => str_replace(':item', (string) ($this->redemption->item->name ?? 'item'), $payload['message']),
            'type' => 'redemption_update',
            'action_url' => route('panel.redemptions.history'),
            'action_label' => 'Ver Histórico',
            'status' => $this->redemption->status,
            'provider_name' => $this->redemption->provider_name,
            'tracking_code' => $this->redemption->tracking_code,
        ];
    }

    private function statusPayload(): array
    {
        return match ((string) $this->redemption->status) {
            'processing' => [
                'label' => 'Em separação',
                'message' => 'Seu pedido de resgate do item **:item** foi aprovado e está em separação.',
            ],
            'shipped' => [
                'label' => 'Enviado',
                'message' => 'Seu pedido de resgate do item **:item** foi enviado.',
            ],
            'completed' => [
                'label' => 'Concluído',
                'message' => 'Seu pedido de resgate do item **:item** foi entregue/concluído.',
            ],
            'cancelled' => [
                'label' => 'Cancelado',
                'message' => 'Seu pedido de resgate do item **:item** foi cancelado. Seus pontos foram devolvidos ao seu saldo.',
            ],
            default => [
                'label' => 'Atualizado',
                'message' => 'Seu pedido de resgate do item **:item** recebeu uma atualização.',
            ],
        };
    }
}
