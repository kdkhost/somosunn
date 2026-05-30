<?php

namespace App\Notifications;

use App\Models\NotificationLog;
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
            ->view('emails.system', [
                'subject' => $this->details['subject'] ?? 'Nova mensagem',
                'content' => $this->details['message'] ?? '',
            ])
            ->subject($this->details['subject'] ?? 'Nova mensagem');
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

    public function sent($notifiable, $channel = null)
    {
        NotificationLog::create([
            'user_id' => $notifiable->id,
            'type' => 'buyer_communication',
            'subject' => $this->details['subject'] ?? 'Nova mensagem',
            'message' => $this->details['message'] ?? '',
            'sent_via_email' => $this->sendEmail,
            'sent_via_database' => true,
            'metadata' => [
                'action_url' => $this->details['action_url'] ?? null,
                'action_label' => $this->details['action_label'] ?? null,
            ],
            'sent_at' => now(),
        ]);
    }
}
