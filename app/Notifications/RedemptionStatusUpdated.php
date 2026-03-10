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
            ->subject('Atualizacao no seu resgate: ' . $payload['label'])
            ->greeting('Ola, ' . $notifiable->name . '!')
            ->line(str_replace(':item', $itemName, $payload['message']));

        if ($this->redemption->provider_name) {
            $mail->line('Responsavel: **' . $this->redemption->provider_name . '**');
        }

        if ($this->redemption->tracking_code) {
            $mail->line('Codigo de rastreio: **' . $this->redemption->tracking_code . '**');
        }

        if ($this->redemption->tracking_url) {
            $mail->line('Acompanhe a entrega: ' . $this->redemption->tracking_url);
        }

        return $mail
            ->action('Ver meus resgates', route('panel.redemptions.history'))
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
            'action_label' => 'Ver historico',
            'status' => $this->redemption->status,
            'provider_name' => $this->redemption->provider_name,
            'tracking_code' => $this->redemption->tracking_code,
        ];
    }

    private function statusPayload(): array
    {
        return match ((string) $this->redemption->status) {
            'processing' => [
                'label' => 'Em separacao',
                'message' => 'Seu pedido de resgate do item **:item** foi aprovado e esta em separacao.',
            ],
            'shipped' => [
                'label' => 'Enviado',
                'message' => 'Seu pedido de resgate do item **:item** foi enviado.',
            ],
            'completed' => [
                'label' => 'Concluido',
                'message' => 'Seu pedido de resgate do item **:item** foi entregue/concluido.',
            ],
            'cancelled' => [
                'label' => 'Cancelado',
                'message' => 'Seu pedido de resgate do item **:item** foi cancelado. Os UNNBIT consumidos foram devolvidos ao seu saldo.',
            ],
            default => [
                'label' => 'Atualizado',
                'message' => 'Seu pedido de resgate do item **:item** recebeu uma atualizacao.',
            ],
        };
    }
}
