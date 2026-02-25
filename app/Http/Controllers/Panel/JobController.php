<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\JobApplication;
use App\Models\MailTemplate;
use App\Notifications\JobApplicationReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function index()
    {
        $vacancies = JobVacancy::where('is_active', true)
            ->whereIn('visibility', ['internal', 'both'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        return view('panel.jobs.index', compact('vacancies'));
    }

    public function show(JobVacancy $job)
    {
        if (!$job->is_active) {
            abort(404);
        }

        $hasApplied = JobApplication::where('job_vacancy_id', $job->id)
            ->where('user_id', Auth::id())
            ->exists();

        return view('panel.jobs.show', compact('job', 'hasApplied'));
    }

    public function apply(Request $request, JobVacancy $job)
    {
        try {
            $fileData = $request->input('file_data');
            $fileName = $request->input('file_name');
            $coverLetter = $request->input('cover_letter');

            if (empty($fileData) || empty($fileName)) {
                return response()->json(['success' => false, 'message' => 'Arquivo do currículo não recebido.'], 422);
            }

            // Verifica candidatura duplicada
            $exists = JobApplication::where('job_vacancy_id', $job->id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Você já se candidatou para esta vaga.'], 409);
            }

            // Valida extensão
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                return response()->json(['success' => false, 'message' => 'Formato inválido. Use PDF, DOC ou DOCX.'], 422);
            }

            // Decodifica Base64
            $rawData = $fileData;
            if (str_contains($rawData, ';base64,')) {
                $rawData = substr($rawData, strpos($rawData, ';base64,') + 8);
            }
            $decoded = base64_decode($rawData);
            if (!$decoded || strlen($decoded) > 2 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'Arquivo inválido ou muito grande (máx 2MB).'], 422);
            }

            // Salva arquivo
            $path = 'resumes/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $decoded);

            $application = JobApplication::create([
                'job_vacancy_id' => $job->id,
                'user_id' => Auth::id(),
                'resume_path' => $path,
                'cover_letter' => $coverLetter,
                'status' => 'pending',
            ]);

            $candidate = Auth::user();

            // 1. Notificação no sino do dono da vaga
            $owner = $job->user;
            if ($owner) {
                $owner->notify(new JobApplicationReceived($application));
            }

            // 2. Email para o CANDIDATO (template: job_apply_candidate)
            $this->sendTemplateEmail('job_apply_candidate', $candidate->email, [
                '{name}' => $candidate->name,
                '{vacancy_title}' => $job->title,
                '{company}' => $job->company_name ?? 'Confidencial',
                '{location}' => $job->location ?? 'Não informado',
                '{site_name}' => config('app.name'),
                '{site_url}' => url('/'),
            ]);

            // 3. Email para o DONO da vaga (template: job_apply_owner)
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

            return response()->json(['success' => true, 'message' => 'Candidatura enviada com sucesso!']);

        } catch (\Throwable $e) {
            Log::error('JobController@apply error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno. Tente novamente.'], 500);
        }
    }

    /** Envia email usando MailTemplate pelo slug (se existir e estiver ativo) */
    private function sendTemplateEmail(string $slug, string $to, array $vars): void
    {
        try {
            $template = MailTemplate::where('slug', $slug)->where('is_active', true)->first();
            if (!$template)
                return;

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
