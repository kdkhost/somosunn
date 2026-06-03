<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use App\Notifications\JobApplicationReceived;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobApplicationService
{
    public function apply(JobVacancy $job, User $candidate, ?string $fileData, ?string $fileName, ?string $coverLetter = null): array
    {
        try {
            if (!$job->is_active || ($job->expires_at && $job->expires_at->isPast())) {
                return [
                    'success' => false,
                    'status' => 404,
                    'message' => 'Esta vaga nao esta mais disponivel.',
                ];
            }

            if (blank($fileData) || blank($fileName)) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Arquivo do curriculo nao recebido.',
                ];
            }

            $exists = JobApplication::where('job_vacancy_id', $job->id)
                ->where('user_id', $candidate->id)
                ->exists();

            if ($exists) {
                return [
                    'success' => false,
                    'status' => 409,
                    'message' => 'Voce ja se candidatou para esta vaga.',
                ];
            }

            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Formato invalido. Use PDF, DOC ou DOCX.',
                ];
            }

            $rawData = $fileData;
            if (str_contains($rawData, ';base64,')) {
                $rawData = substr($rawData, strpos($rawData, ';base64,') + 8);
            }

            $decoded = base64_decode($rawData);
            if (!$decoded || strlen($decoded) > 2 * 1024 * 1024) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Arquivo invalido ou muito grande (max 2MB).',
                ];
            }

            $path = 'resumes/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $decoded);

            $application = JobApplication::create([
                'job_vacancy_id' => $job->id,
                'user_id' => $candidate->id,
                'resume_path' => $path,
                'cover_letter' => $coverLetter,
                'status' => 'pending',
            ]);

            $owner = $job->user;
            if ($owner) {
                $owner->notify(new JobApplicationReceived($application));
            }

            $this->sendTemplateEmail('job_apply_candidate', $candidate->email, [
                '{name}' => $candidate->name,
                '{vacancy_title}' => $job->title,
                '{company}' => $job->company_name ?? 'Confidencial',
                '{location}' => $job->location ?? 'Nao informado',
                '{site_name}' => config('app.name'),
                '{site_url}' => url('/'),
            ]);

            if ($owner) {
                $this->sendTemplateEmail('job_apply_owner', $owner->email, [
                    '{owner_name}' => $owner->name,
                    '{candidate}' => $candidate->name,
                    '{vacancy_title}' => $job->title,
                    '{candidates_url}' => route('panel.my-jobs.candidates', $job),
                    '{site_name}' => config('app.name'),
                    '{site_url}' => url('/'),
                ]);
            }

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Candidatura enviada com sucesso!',
                'application_id' => $application->id,
            ];
        } catch (\Throwable $e) {
            Log::error('JobApplicationService@apply error: ' . $e->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Erro interno. Tente novamente.',
            ];
        }
    }

    private function sendTemplateEmail(string $slug, string $to, array $vars): void
    {
        try {
            $bladeVars = [];
            foreach ($vars as $k => $v) {
                $cleanKey = trim($k, '{}');
                $bladeVars[$cleanKey] = $v;
            }

            app(SystemMailTemplateService::class)->send(
                $slug,
                $to,
                $bladeVars,
                $this->defaultApplicationTemplates()[$slug]
            );
        } catch (\Throwable $e) {
            Log::error("sendTemplateEmail[$slug] error: " . $e->getMessage());
        }
    }

    private function defaultApplicationTemplates(): array
    {
        return [
            'job_apply_candidate' => [
                'name' => 'Candidatura Recebida pelo Candidato',
                'category' => 'vagas',
                'subject' => 'Candidatura enviada para {{vacancy_title}}',
                'body' => '<h2>Ola, {{name}}!</h2><p>Sua candidatura para <strong>{{vacancy_title}}</strong>, na empresa {{company}}, foi enviada com sucesso.</p><p>Local: {{location}}</p>',
            ],
            'job_apply_owner' => [
                'name' => 'Nova Candidatura para Responsavel da Vaga',
                'category' => 'vagas',
                'subject' => 'Nova candidatura para {{vacancy_title}}',
                'body' => '<h2>Ola, {{owner_name}}!</h2><p><strong>{{candidate}}</strong> candidatou-se para {{vacancy_title}}.</p><p><a href="{{candidates_url}}">Ver candidatos</a></p>',
            ],
        ];
    }
}
