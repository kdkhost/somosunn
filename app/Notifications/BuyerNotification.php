<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BuyerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $details;
    protected $sendEmail;

    public function __construct(array $details, bool $sendEmail = false)
    {
        $this->details = $details;
        $this->sendEmail = $sendEmail;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($this->sendEmail) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->details['subject'] ?? 'Nova mensagem')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line($this->details['message'] ?? '')
            ->line('Obrigado por fazer parte da UNN.')
            ->salutation('Equipe UNN');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->details['message'] ?? 'Nova mensagem',
            'type' => 'buyer_communication',
            'action_url' => $this->details['action_url'] ?? null,
            'action_label' => $this->details['action_label'] ?? null,
        ];
    }
}
