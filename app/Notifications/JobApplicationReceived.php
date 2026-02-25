<?php

namespace App\Notifications;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobApplicationReceived extends Notification
{
    use Queueable;

    protected JobApplication $application;
    protected JobVacancy $vacancy;

    public function __construct(JobApplication $application)
    {
        $this->application = $application;
        $this->vacancy = $application->vacancy;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $candidate = $this->application->user;

        return [
            'message' => 'Nova candidatura de ' . ($candidate->name ?? 'Alguém') . ' para a vaga "' . $this->vacancy->title . '"',
            'type' => 'job_application',
            'action_url' => route('panel.my-jobs.candidates', $this->vacancy),
            'action_label' => 'Ver candidatos',
            'vacancy_title' => $this->vacancy->title,
            'candidate_name' => $candidate->name ?? '',
            'application_id' => $this->application->id,
        ];
    }
}
