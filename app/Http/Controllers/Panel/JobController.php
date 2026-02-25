<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // Aceita JSON (fetch) ou form tradicional
            $fileData = $request->input('file_data');
            $fileName = $request->input('file_name');
            $coverLetter = $request->input('cover_letter');

            if (empty($fileData) || empty($fileName)) {
                return response()->json(['success' => false, 'message' => 'Arquivo do currículo não recebido.'], 422);
            }

            // Verifica se já candidatou
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

            // Salva o arquivo
            $path = 'resumes/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, $decoded);

            JobApplication::create([
                'job_vacancy_id' => $job->id,
                'user_id' => Auth::id(),
                'resume_path' => $path,
                'cover_letter' => $coverLetter,
                'status' => 'pending'
            ]);

            return response()->json(['success' => true, 'message' => 'Candidatura enviada com sucesso!']);

        } catch (\Throwable $e) {
            Log::error('JobController@apply error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno. Tente novamente.'], 500);
        }
    }
}
