<?php

namespace App\Notifications;

use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobVacancyPublished extends Notification
{
    use Queueable;

    public function __construct(protected $vacancy)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return app(SystemMailTemplateService::class)->mailMessage('job_vacancy_published', [
            'user' => ['name' => $notifiable->name ?? 'Membro'],
            'vacancy' => [
                'title' => $this->vacancy->title,
                'company' => $this->vacancy->company_name ?? 'Confidencial',
                'location' => $this->vacancy->location ?? 'Nao informado',
                'url' => $this->vacancyUrl(),
            ],
        ], [
            'name' => 'Nova Vaga Publicada',
            'category' => 'vagas',
            'subject' => 'Nova oportunidade: {{vacancy.title}}',
            'body' => '<h2>Ola, {{user.name}}!</h2><p>Uma nova vaga foi publicada.</p><p><strong>Vaga:</strong> {{vacancy.title}}<br><strong>Empresa:</strong> {{vacancy.company}}<br><strong>Localizacao:</strong> {{vacancy.location}}</p><p><a href="{{vacancy.url}}">Ver detalhes da vaga</a></p>',
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Nova vaga publicada: ' . $this->vacancy->title,
            'type' => 'job_vacancy',
            'action_url' => $this->vacancyUrl(),
            'action_label' => 'Candidatar-se',
            'company' => $this->vacancy->company_name,
        ];
    }

    private function vacancyUrl(): string
    {
        return route('jobs.public.show', $this->vacancy) . '#candidatura';
    }
}
