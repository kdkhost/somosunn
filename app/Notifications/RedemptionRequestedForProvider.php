<?php

namespace App\Notifications;

use App\Models\Redemption;
use App\Models\User;
use App\Services\Mail\SystemMailTemplateService;
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

        return app(SystemMailTemplateService::class)->mailMessage('redemption_requested_provider', [
            'redemption' => [
                'buyer_name' => $buyerName,
                'item_name' => $itemName,
                'item_type' => $this->redemption->item_type_label,
                'points' => number_format((int) $this->redemption->points_spent, 0, ',', '.'),
                'reference_value' => $this->redemption->reference_value !== null ? 'R$ ' . number_format((float) $this->redemption->reference_value, 2, ',', '.') : '',
                'delivery_date' => $this->redemption->estimated_delivery_at?->format('d/m/Y') ?? '',
                'instructions' => strip_tags((string) ($this->redemption->fulfillment_instructions ?? '')),
                'action_url' => $actionUrl ?? '',
            ],
        ], [
            'name' => 'Novo Resgate para Fornecedor',
            'category' => 'sistema',
            'subject' => 'Novo resgate com UNNBIT: {{redemption.item_name}}',
            'body' => '<h2>Novo resgate recebido</h2><p><strong>Comprador:</strong> {{redemption.buyer_name}}<br><strong>Item:</strong> {{redemption.item_name}}<br><strong>Tipo:</strong> {{redemption.item_type}}<br><strong>UNNBIT:</strong> {{redemption.points}}</p><p><a href="{{redemption.action_url}}">Abrir gestao de resgates</a></p>',
        ]);
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
