<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $details;

    /**
     * Create a new notification instance.
     * 
     * @param array $details Must contain 'message', 'type', 'action_url', 'action_label'
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->details['message'] ?? 'Nova notificação',
            'type' => $this->details['type'] ?? 'info',
            'action_url' => $this->details['action_url'] ?? '#',
            'action_label' => $this->details['action_label'] ?? 'Ver detalhes',
        ];
    }
}
