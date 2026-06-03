<?php

namespace App\Notifications;

use App\Models\OrderItem;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected OrderItem $orderItem)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return app(SystemMailTemplateService::class)->mailMessage('feedback_request', [
            'user' => ['name' => $notifiable->name],
            'item' => ['name' => $this->orderItem->title ?? 'Produto/Servico'],
            'feedback' => ['url' => route('panel.reviews.create', ['order_item' => $this->orderItem->id])],
        ], [
            'name' => 'Solicitacao de Avaliacao',
            'category' => 'marketing',
            'subject' => 'Avalie sua experiencia: {{item.name}}',
            'body' => '<h2>Ola, {{user.name}}!</h2><p>Gostariamos de saber sua opiniao sobre <strong>{{item.name}}</strong>.</p><p><a href="{{feedback.url}}">Deixar avaliacao</a></p>',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Deixe sua avaliacao para ' . ($this->orderItem->title ?? 'Produto/Servico'),
            'type' => 'feedback_request',
            'action_url' => route('panel.reviews.create', ['order_item' => $this->orderItem->id]),
            'action_label' => 'Deixar Avaliacao',
        ];
    }
}
