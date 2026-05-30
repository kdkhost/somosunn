<?php

namespace App\Notifications;

use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $orderItem;

    public function __construct(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $itemName = $this->orderItem->title ?? 'Produto/Serviço';
        $feedbackUrl = route('panel.reviews.create', ['order_item' => $this->orderItem->id]);

        return (new MailMessage)
            ->view('emails.system', [
                'subject' => 'Avalie sua experiência: ' . $itemName,
                'content' => "Olá {$notifiable->name}!\n\n" .
                    "Já se passaram 14 dias desde sua compra de {$itemName}.\n\n" .
                    "Gostaríamos muito de saber sua opinião sobre o produto/serviço. Sua avaliação é muito importante para nós e para outros compradores.\n\n" .
                    "Por favor, reserve um momento para deixar sua avaliação clicando no botão abaixo.",
            ])
            ->subject('Avalie sua experiência: ' . $itemName)
            ->action('Deixar Avaliação', $feedbackUrl)
            ->line('Obrigado por fazer parte da UNN!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Deixe sua avaliação para ' . ($this->orderItem->title ?? 'Produto/Serviço'),
            'type' => 'feedback_request',
            'action_url' => route('panel.reviews.create', ['order_item' => $this->orderItem->id]),
            'action_label' => 'Deixar Avaliação',
        ];
    }
}
