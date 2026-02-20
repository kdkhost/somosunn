<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobVacancyPublished extends Notification
{
    use Queueable;

    protected $vacancy;

    /**
     * Create a new notification instance.
     */
    public function __construct($vacancy)
    {
        $this->vacancy = $vacancy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nova Oportunidade: ' . $this->vacancy->title)
            ->greeting('Olá, membro SOMOS UNN!')
            ->line('Uma nova vaga de emprego acaba de ser publicada na nossa comunidade.')
            ->line('**Vaga:** ' . $this->vacancy->title)
            ->line('**Empresa:** ' . ($this->vacancy->company_name ?? 'Confidencial'))
            ->line('**Localização:** ' . ($this->vacancy->location ?? 'Não informado'))
            ->action('Ver Detalhes da Vaga', route('panel.jobs.show', $this->vacancy))
            ->line('Aproveite esta oportunidade estratégica de networking e carreira!')
            ->salutation('Atenciosamente, Equipe SOMOS UNN');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Nova vaga publicada: ' . $this->vacancy->title,
            'type' => 'job_vacancy',
            'action_url' => route('panel.jobs.show', $this->vacancy),
            'action_label' => 'Candidatar-se',
            'company' => $this->vacancy->company_name,
        ];
    }
}
