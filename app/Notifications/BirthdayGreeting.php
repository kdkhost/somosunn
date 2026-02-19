<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BirthdayGreeting extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Can add 'database' if needed
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Parabéns pelo seu dia! 🎉')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Hoje é um dia especial e não poderíamos deixar de passar para te desejar um Feliz Aniversário!')
            ->line('Que seu novo ciclo seja repleto de conquistas, saúde e muito sucesso.')
            ->action('Acessar a Plataforma', url('/'))
            ->line('Conte sempre conosco!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
