<?php

namespace App\Notifications;

use App\Models\Redemption;
use App\Models\User;
use App\Support\EmailQueueSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RedemptionRequestedForProvider extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Redemption $redemption)
    {
        $this->onConnection(EmailQueueSettings::connection());
        $this->onQueue(EmailQueueSettings::queueName());

        if (EmailQueueSettings::shouldQueue() && EmailQueueSettings::delaySeconds() > 0) {
            $this->delay(now()->addSeconds(EmailQueueSettings::delaySeconds()));
        }
    }

    public function via($notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $itemName = (string) ($this->redemption->item->name ?? 'Item resgatado');
        $buyerName = (string) ($this->redemption->user->name ?? 'Membro');
        $actionUrl = $this->actionUrl($notifiable);

        $mail = (new MailMessage)
            ->subject('Novo resgate com UNNBIT: ' . $itemName)
            ->greeting('Ola!')
            ->line('Um membro acabou de trocar UNNBIT por um item do seu catalogo.')
            ->line('**Comprador:** ' . $buyerName)
            ->line('**Item:** ' . $itemName)
            ->line('**Tipo:** ' . $this->redemption->item_type_label)
            ->line('**UNNBIT consumidos:** ' . number_format((int) $this->redemption->points_spent, 0, ',', '.') . ' UNNBIT');

        if ($this->redemption->reference_value !== null) {
            $mail->line('**Valor de referencia:** R$ ' . number_format((float) $this->redemption->reference_value, 2, ',', '.'));
        }

        if ($this->redemption->estimated_delivery_at) {
            $mail->line('**Prazo previsto:** ' . $this->redemption->estimated_delivery_at->format('d/m/Y'));
        }

        if ($this->redemption->fulfillment_instructions) {
            $mail->line('**Regras de entrega/fornecimento:** ' . strip_tags((string) $this->redemption->fulfillment_instructions));
        }

        if ($actionUrl !== null) {
            $mail->action('Abrir gestao de resgates', $actionUrl);
        }

        return $mail
            ->line('O item deve ser entregue conforme o tipo cadastrado no sistema e alinhado com o comprador dentro das regras da plataforma.')
            ->salutation('Equipe SOMOS UNN');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'Novo resgate recebido para o item "' . (string) ($this->redemption->item->name ?? 'item') . '".',
            'type' => 'redemption_provider_request',
            'action_url' => $this->actionUrl($notifiable),
            'action_label' => 'Abrir resgates',
            'redemption_id' => $this->redemption->id,
            'item_name' => (string) ($this->redemption->item->name ?? 'Item resgatado'),
            'buyer_name' => (string) ($this->redemption->user->name ?? 'Membro'),
            'points_spent' => (int) $this->redemption->points_spent,
            'item_type' => (string) ($this->redemption->item_type ?: 'service'),
        ];
    }

    private function actionUrl(object $notifiable): ?string
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return null;
        }

        if ($notifiable instanceof User && $notifiable->isAdmin()) {
            return route('admin.redemptions.index');
        }

        return route('panel.admin.redemptions.index');
    }
}
