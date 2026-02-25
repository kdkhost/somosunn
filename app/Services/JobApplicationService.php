<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\MailTemplate;
use App\Models\User;
use App\Notifications\JobApplicationReceived;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            $template = MailTemplate::where('slug', $slug)->where('is_active', true)->first();
            if (!$template) {
                return;
            }

            $body = str_replace(array_keys($vars), array_values($vars), $template->body);
            $subject = str_replace(array_keys($vars), array_values($vars), $template->subject);

            Mail::html($body, function ($m) use ($to, $subject) {
                $m->to($to)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error("sendTemplateEmail[$slug] error: " . $e->getMessage());
        }
    }
}

